<?php

namespace App\Services\Payment;

use App\Enums\TransactionStatus;
use App\Exceptions\ApiException;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Midtrans Snap: satu halaman pembayaran untuk Virtual Account, kartu kredit
 * (termasuk cicilan bank), e-wallet (GoPay/ShopeePay), dan QRIS.
 */
class MidtransGateway implements PaymentGatewayInterface
{
    public function name(): string
    {
        return 'midtrans';
    }

    public function createCharge(Transaction $transaction, User $payer): ChargeResult
    {
        $amount = (int) round((float) $transaction->amount);

        $payload = [
            'transaction_details' => [
                'order_id' => $transaction->code,
                'gross_amount' => $amount,
            ],
            'item_details' => [[
                'id' => $transaction->code,
                'price' => $amount,
                'quantity' => 1,
                'name' => Str::limit($transaction->description, 50, ''),
            ]],
            'customer_details' => array_filter([
                'first_name' => Str::limit($payer->name, 100, ''),
                'email' => $payer->email,
                'phone' => $payer->phone,
            ]),
            'enabled_payments' => config('payments.midtrans.enabled_payments'),
            'expiry' => ['unit' => 'hour', 'duration' => (int) config('payments.expiry_hours', 24)],
        ];

        if ($finishUrl = config('payments.finish_url')) {
            $payload['callbacks'] = ['finish' => $finishUrl];
        }

        // Cicilan kartu kredit hanya ditawarkan untuk nominal yang memenuhi syarat bank.
        if ($amount >= (int) config('payments.midtrans.installment_min_amount')) {
            $payload['credit_card'] = [
                'secure' => true,
                'installment' => [
                    'required' => false,
                    'terms' => config('payments.midtrans.installment_terms'),
                ],
            ];
        }

        $response = Http::withBasicAuth($this->serverKey(), '')
            ->acceptJson()
            ->timeout(15)
            ->post($this->snapUrl(), $payload);

        if ($response->failed() || ! $response->json('token')) {
            Log::error('Midtrans createCharge gagal', [
                'order_id' => $transaction->code,
                'status' => $response->status(),
                'body' => $response->json(),
            ]);

            throw new ApiException('Gagal membuat tagihan pembayaran. Silakan coba lagi.', 502);
        }

        return new ChargeResult(
            token: $response->json('token'),
            redirectUrl: (string) $response->json('redirect_url'),
            raw: $response->json(),
        );
    }

    public function parseNotification(array $payload): GatewayNotification
    {
        $expected = hash(
            'sha512',
            ($payload['order_id'] ?? '').($payload['status_code'] ?? '').($payload['gross_amount'] ?? '').$this->serverKey()
        );

        if (! hash_equals($expected, (string) ($payload['signature_key'] ?? ''))) {
            Log::warning('Midtrans webhook: signature tidak valid', ['order_id' => $payload['order_id'] ?? null]);

            throw new ApiException('Signature tidak valid.', 403);
        }

        return $this->toNotification($payload);
    }

    public function fetchStatus(string $orderId): GatewayNotification
    {
        $response = Http::withBasicAuth($this->serverKey(), '')
            ->acceptJson()
            ->timeout(10)
            ->get($this->apiUrl().'/v2/'.rawurlencode($orderId).'/status');

        if ($response->failed()) {
            throw new ApiException('Gagal mengambil status dari payment gateway.', 502);
        }

        $payload = $response->json();

        if ((string) ($payload['status_code'] ?? '') === '404') {
            throw new ApiException('Transaksi belum tercatat di payment gateway (pembayaran belum dimulai).', 404);
        }

        return $this->toNotification($payload);
    }

    // ------------------------------------------------------------------

    private function toNotification(array $p): GatewayNotification
    {
        $txStatus = (string) ($p['transaction_status'] ?? '');
        $fraud = $p['fraud_status'] ?? null;

        [$status, $needsReview] = match (true) {
            $txStatus === 'capture' && $fraud === 'challenge' => [TransactionStatus::Pending, true],
            in_array($txStatus, ['capture', 'settlement'], true) => [TransactionStatus::Paid, false],
            $txStatus === 'pending' => [TransactionStatus::Pending, false],
            in_array($txStatus, ['deny', 'failure'], true) => [TransactionStatus::Failed, false],
            $txStatus === 'cancel' => [TransactionStatus::Cancelled, false],
            $txStatus === 'expire' => [TransactionStatus::Expired, false],
            in_array($txStatus, ['refund', 'partial_refund', 'chargeback', 'partial_chargeback'], true) => [TransactionStatus::Refunded, false],
            default => [TransactionStatus::Pending, false],
        };

        $channel = $p['va_numbers'][0]['bank']
            ?? $p['bank']
            ?? (isset($p['permata_va_number']) ? 'permata' : (isset($p['biller_code']) ? 'mandiri' : null));

        return new GatewayNotification(
            orderId: (string) ($p['order_id'] ?? ''),
            status: $status,
            grossAmount: (float) ($p['gross_amount'] ?? 0),
            paymentType: $p['payment_type'] ?? null,
            channel: $channel,
            gatewayTransactionId: $p['transaction_id'] ?? null,
            fraudStatus: $fraud,
            installmentTerm: isset($p['installment_term']) && is_numeric($p['installment_term']) ? (int) $p['installment_term'] : null,
            needsReview: $needsReview,
            raw: $p,
        );
    }

    private function serverKey(): string
    {
        $key = (string) config('payments.midtrans.server_key');

        if ($key === '') {
            throw new ApiException('Payment gateway belum dikonfigurasi.', 503);
        }

        return $key;
    }

    private function snapUrl(): string
    {
        return config('payments.midtrans.is_production')
            ? 'https://app.midtrans.com/snap/v1/transactions'
            : 'https://app.sandbox.midtrans.com/snap/v1/transactions';
    }

    private function apiUrl(): string
    {
        return config('payments.midtrans.is_production')
            ? 'https://api.midtrans.com'
            : 'https://api.sandbox.midtrans.com';
    }
}
