<?php

namespace App\Services;

use App\Enums\BookingStatus;
use App\Enums\PropertyStatus;
use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Exceptions\ApiException;
use App\Models\Property;
use App\Models\Transaction;
use App\Models\User;
use App\Repositories\Contracts\PropertyRepositoryInterface;
use App\Repositories\Contracts\TransactionRepositoryInterface;
use App\Services\Payment\GatewayNotification;
use App\Services\Payment\PaymentGatewayInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PaymentService
{
    public function __construct(
        private TransactionRepositoryInterface $transactions,
        private PropertyRepositoryInterface $properties,
        private PaymentGatewayInterface $gateway,
    ) {
    }

    // =================== CHECKOUT ===================

    /** Booking fee (tanda jadi) untuk membeli properti. */
    public function checkoutProperty(User $buyer, int $propertyId): Transaction
    {
        return DB::transaction(function () use ($buyer, $propertyId) {
            // Kunci baris properti: dua pembeli tidak bisa checkout bersamaan.
            $property = $this->transactions->lockProperty($propertyId);

            if (! $property || $property->status !== PropertyStatus::Published) {
                throw new ApiException('Properti tidak tersedia untuk dipesan.', 422);
            }

            if ($property->partner_id === $buyer->id) {
                throw new ApiException('Anda tidak dapat memesan properti milik sendiri.', 422);
            }

            if ($existing = $this->transactions->activePendingForProperty($property->id)) {
                if ($existing->user_id !== $buyer->id) {
                    throw new ApiException('Properti sedang dalam proses pembayaran oleh pembeli lain.', 409);
                }

                return $existing; // lanjutkan tagihan yang sama
            }

            /** @var Transaction $transaction */
            $transaction = $this->transactions->create([
                'code' => $this->generateCode(),
                'user_id' => $buyer->id,
                'payee_id' => $property->partner_id,
                'type' => TransactionType::PropertyBookingFee,
                'property_id' => $property->id,
                'description' => 'Booking fee: '.$property->title,
                'amount' => $this->bookingFeeFor($property),
                'status' => TransactionStatus::Pending,
                'gateway' => $this->gateway->name(),
                'expires_at' => now()->addHours((int) config('payments.expiry_hours', 24)),
            ]);

            return $this->attachCharge($transaction, $buyer);
        });
    }

    /** Pembayaran jasa konsultasi arsitek (setelah pesanan dikonfirmasi arsitek). */
    public function checkoutBooking(User $client, int $bookingId): Transaction
    {
        return DB::transaction(function () use ($client, $bookingId) {
            $booking = $this->transactions->lockBooking($bookingId);

            if (! $booking || $booking->client_id !== $client->id) {
                throw new ApiException('Pesanan konsultasi tidak ditemukan.', 404);
            }

            if ($booking->status !== BookingStatus::Confirmed) {
                throw new ApiException('Pembayaran hanya dapat dilakukan setelah pesanan dikonfirmasi arsitek.', 422);
            }

            if ($this->transactions->hasPaidForBooking($booking->id)) {
                throw new ApiException('Pesanan ini sudah dibayar.', 422);
            }

            if ($existing = $this->transactions->activePendingForBooking($booking->id)) {
                return $existing;
            }

            /** @var Transaction $transaction */
            $transaction = $this->transactions->create([
                'code' => $this->generateCode(),
                'user_id' => $client->id,
                'payee_id' => $booking->architect_id,
                'type' => TransactionType::ArchitectBooking,
                'architect_booking_id' => $booking->id,
                'description' => 'Konsultasi arsitek '.$booking->code,
                'amount' => $booking->price,
                'status' => TransactionStatus::Pending,
                'gateway' => $this->gateway->name(),
                'expires_at' => now()->addHours((int) config('payments.expiry_hours', 24)),
            ]);

            return $this->attachCharge($transaction, $client);
        });
    }

    // =================== WEBHOOK & SINKRONISASI ===================

    /** Dipanggil endpoint webhook gateway. Mengembalikan null bila order_id tidak dikenal. */
    public function handleNotification(array $payload): ?Transaction
    {
        return $this->apply($this->gateway->parseNotification($payload));
    }

    /** Rekonsiliasi manual: tarik status langsung dari gateway. */
    public function sync(Transaction $transaction): Transaction
    {
        return $this->apply($this->gateway->fetchStatus($transaction->code)) ?? $transaction;
    }

    private function apply(GatewayNotification $n): ?Transaction
    {
        return DB::transaction(function () use ($n) {
            $transaction = $this->transactions->findByCodeForUpdate($n->orderId);

            if (! $transaction) {
                Log::notice('Notifikasi pembayaran untuk order tidak dikenal', ['order_id' => $n->orderId]);

                return null;
            }

            // Nominal dari gateway harus sama dengan yang kita catat.
            if (abs((float) $transaction->amount - $n->grossAmount) > 0.5) {
                Log::error('Nominal notifikasi tidak cocok', [
                    'order_id' => $n->orderId,
                    'expected' => (string) $transaction->amount,
                    'received' => $n->grossAmount,
                ]);

                throw new ApiException('Nominal pembayaran tidak sesuai.', 422);
            }

            // Idempotent: notifikasi ganda / urutan terbalik tidak merusak status final.
            if (! $this->canTransition($transaction->status, $n->status)) {
                return $transaction;
            }

            $attributes = [
                'status' => $n->status,
                'payment_type' => $n->paymentType ?? $transaction->payment_type,
                'payment_channel' => $n->channel ?? $transaction->payment_channel,
                'gateway_transaction_id' => $n->gatewayTransactionId ?? $transaction->gateway_transaction_id,
                'fraud_status' => $n->fraudStatus,
                'installment_term' => $n->installmentTerm ?? $transaction->installment_term,
                'gateway_payload' => $n->raw,
            ];

            $latePayment = $n->status === TransactionStatus::Paid && $transaction->status !== TransactionStatus::Pending;

            if ($n->needsReview || $latePayment) {
                $attributes['needs_review'] = true;
            }

            if ($n->status === TransactionStatus::Paid && $transaction->paid_at === null) {
                $attributes['paid_at'] = now();
            }

            /** @var Transaction $transaction */
            $transaction = $this->transactions->update($transaction, $attributes);

            if ($n->status === TransactionStatus::Paid) {
                $this->onPaid($transaction);
            }

            return $transaction;
        });
    }

    private function canTransition(TransactionStatus $from, TransactionStatus $to): bool
    {
        return match ($from) {
            TransactionStatus::Pending => true,
            TransactionStatus::Paid => $to === TransactionStatus::Refunded,
            TransactionStatus::Refunded => false,
            // failed / expired / cancelled: hanya "pembayaran terlambat" yang masih berarti
            default => $to === TransactionStatus::Paid,
        };
    }

    /** Efek bisnis setelah lunas. Anomali ditandai needs_review agar dicek admin. */
    private function onPaid(Transaction $transaction): void
    {
        if ($transaction->type === TransactionType::PropertyBookingFee) {
            $property = $this->transactions->lockProperty((int) $transaction->property_id);

            if ($property && $property->status === PropertyStatus::Published) {
                $this->properties->update($property, ['status' => PropertyStatus::Reserved]);

                return;
            }

            $this->transactions->update($transaction, ['needs_review' => true]);

            return;
        }

        if ($transaction->type === TransactionType::ArchitectBooking) {
            $booking = $this->transactions->lockBooking((int) $transaction->architect_booking_id);

            if (! $booking || ! in_array($booking->status, [BookingStatus::Confirmed, BookingStatus::Completed], true)) {
                $this->transactions->update($transaction, ['needs_review' => true]);
            }
        }
    }

    // =================== HELPER ===================

    private function attachCharge(Transaction $transaction, User $payer): Transaction
    {
        $charge = $this->gateway->createCharge($transaction, $payer);

        /** @var Transaction $transaction */
        $transaction = $this->transactions->update($transaction, [
            'snap_token' => $charge->token,
            'redirect_url' => $charge->redirectUrl,
        ]);

        return $transaction;
    }

    private function bookingFeeFor(Property $property): float
    {
        $cfg = config('payments.property_booking_fee');
        $price = (float) $property->price;

        $fee = $price * ((float) $cfg['percent'] / 100);
        $fee = min(max($fee, (float) $cfg['min']), (float) $cfg['max'], $price);

        return (float) round($fee, -3); // bulatkan ke ribuan
    }

    private function generateCode(): string
    {
        do {
            $code = 'TRX-'.now()->format('ymd').'-'.strtoupper(Str::random(8));
        } while ($this->transactions->findByCode($code) !== null);

        return $code;
    }
}
