<?php

namespace App\Services;

use App\Enums\LeadStatus;
use App\Enums\PortfolioStatus;
use App\Enums\PropertyStatus;
use App\Enums\RoleName;
use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Exceptions\ApiException;
use App\Models\ArchitectPortfolio;
use App\Models\ConciergeLead;
use App\Models\Transaction;
use App\Models\User;
use App\Repositories\Contracts\ArchitectPortfolioRepositoryInterface;
use App\Repositories\Contracts\ConciergeLeadRepositoryInterface;
use App\Repositories\Contracts\ConciergeTransactionRepositoryInterface;
use App\Repositories\Contracts\TransactionRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Services\Payment\PaymentGatewayInterface;
use App\Support\ConciergeFees;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

/**
 * Cockpit admin: invoice Midtrans Snap instan untuk pembeli yang sudah sepakat lewat WhatsApp.
 * Webhook Midtrans TIDAK diubah: status lunas tetap ditangani PaymentService (properti -> reserved).
 */
class AdminInvoiceService
{
    public function __construct(
        private TransactionRepositoryInterface $transactions,
        private ConciergeTransactionRepositoryInterface $concierge,
        private ArchitectPortfolioRepositoryInterface $portfolios,
        private ConciergeLeadRepositoryInterface $leads,
        private UserRepositoryInterface $users,
        private PaymentService $payments,
        private PaymentGatewayInterface $gateway,
    ) {
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{transaction: Transaction, commission_percent: float, fee_basis: string, reused: bool, wa_message: string}
     */
    public function createQuickBooking(User $admin, array $data): array
    {
        $buyer = $this->resolveBuyer($data);
        $type = TransactionType::from($data['type']);
        $lead = $this->resolveLead($data['lead_code'] ?? null);

        $result = DB::transaction(fn () => match ($type) {
            TransactionType::PropertyBookingFee => $this->propertyInvoice($admin, $buyer, $lead, $data),
            TransactionType::ArchitectBlueprint => $this->blueprintInvoice($admin, $buyer, $lead, $data),
            TransactionType::ArchitectCustomBooking => $this->customInvoice($admin, $buyer, $lead, $data),
            default => throw new ApiException('Jenis invoice tidak didukung.', 422),
        });

        if ($lead && ! $result['reused']) {
            $this->leads->update($lead, [
                'status' => LeadStatus::Committed->value,
                'handled_by' => $lead->handled_by ?? $admin->id,
            ]);
        }

        $result['wa_message'] = $this->waMessage($result['transaction'], $buyer);

        return $result;
    }

    // ---------- Jenis invoice ----------

    private function propertyInvoice(User $admin, User $buyer, ?ConciergeLead $lead, array $data): array
    {
        // Kunci baris properti: dua pembeli tidak bisa ditagih bersamaan untuk properti yang sama.
        $property = $this->transactions->lockProperty((int) $data['property_id']);

        if (! $property || $property->status !== PropertyStatus::Published || ! $property->partner?->isActive()) {
            throw new ApiException('Properti tidak tersedia untuk dipesan.', 422);
        }

        if ($existing = $this->transactions->activePendingForProperty($property->id)) {
            if ($existing->user_id !== $buyer->id) {
                throw new ApiException('Properti sedang dalam proses pembayaran oleh pembeli lain.', 409);
            }

            return $this->result($existing, ConciergeFees::propertyPercent(), 'property_price', true);
        }

        $amount = isset($data['amount']) ? (float) $data['amount'] : $this->payments->bookingFeeFor($property);

        if ($amount > (float) $property->price) {
            throw new ApiException('Nominal booking fee tidak boleh melebihi harga properti.', 422);
        }

        // Management fee 2% dihitung dari HARGA properti; booking fee hanyalah uang muka transaksi.
        $fee = ConciergeFees::propertyFee((float) $property->price);

        $transaction = $this->issue($buyer, $admin, $lead, $data, [
            'payee_id' => $property->partner_id,
            'type' => TransactionType::PropertyBookingFee,
            'property_id' => $property->id,
            'description' => 'Booking fee: '.$property->title,
            'amount' => $amount,
            'platform_fee' => $fee,
            'net_partner_amount' => max(0.0, $amount - $fee),
        ]);

        return $this->result($transaction, ConciergeFees::propertyPercent(), 'property_price', false);
    }

    private function blueprintInvoice(User $admin, User $buyer, ?ConciergeLead $lead, array $data): array
    {
        $portfolio = $this->publishedPortfolio((int) $data['portfolio_id']);

        if (! $portfolio->is_ready_to_build || ! $portfolio->blueprint_price) {
            throw new ApiException('Portofolio ini belum menyediakan paket desain siap bangun.', 422);
        }

        $percent = $this->commissionPercent($data);

        if ($existing = $this->concierge->activePendingForPortfolio($buyer->id, $portfolio->id, TransactionType::ArchitectBlueprint->value)) {
            return $this->result($existing, $percent, 'invoice_amount', true);
        }

        $amount = (float) $portfolio->blueprint_price;
        $fee = ConciergeFees::architectFee($amount, $percent);

        $transaction = $this->issue($buyer, $admin, $lead, $data, [
            'payee_id' => $portfolio->user_id,
            'type' => TransactionType::ArchitectBlueprint,
            'portfolio_id' => $portfolio->id,
            'description' => 'Paket desain siap bangun: '.$portfolio->title,
            'amount' => $amount,
            'platform_fee' => $fee,
            'net_partner_amount' => $amount - $fee,
        ]);

        return $this->result($transaction, $percent, 'invoice_amount', false);
    }

    private function customInvoice(User $admin, User $buyer, ?ConciergeLead $lead, array $data): array
    {
        $portfolio = $this->publishedPortfolio((int) $data['portfolio_id']);
        $percent = $this->commissionPercent($data);

        if ($existing = $this->concierge->activePendingForPortfolio($buyer->id, $portfolio->id, TransactionType::ArchitectCustomBooking->value)) {
            return $this->result($existing, $percent, 'invoice_amount', true);
        }

        if (! isset($data['amount'])) {
            throw new ApiException('Nominal tanda jadi (amount) wajib diisi untuk jalur custom.', 422);
        }

        $amount = (float) $data['amount'];
        $fee = ConciergeFees::architectFee($amount, $percent);

        $transaction = $this->issue($buyer, $admin, $lead, $data, [
            'payee_id' => $portfolio->user_id,
            'type' => TransactionType::ArchitectCustomBooking,
            'portfolio_id' => $portfolio->id,
            'description' => 'Tanda jadi desain custom: '.$portfolio->title,
            'amount' => $amount,
            'platform_fee' => $fee,
            'net_partner_amount' => $amount - $fee,
        ]);

        return $this->result($transaction, $percent, 'invoice_amount', false);
    }

    // ---------- Helper ----------

    /** @param  array<string, mixed>  $attributes */
    private function issue(User $buyer, User $admin, ?ConciergeLead $lead, array $data, array $attributes): Transaction
    {
        $hours = min(
            (int) ($data['expires_in_hours'] ?? config('payments.expiry_hours', 24)),
            (int) config('concierge.invoice.max_expiry_hours', 72),
        );

        /** @var Transaction $transaction */
        $transaction = $this->transactions->create($attributes + [
            'code' => $this->generateCode(),
            'user_id' => $buyer->id,
            'status' => TransactionStatus::Pending,
            'gateway' => $this->gateway->name(),
            'lead_id' => $lead?->id,
            'issued_by' => $admin->id,
            'expires_at' => now()->addHours(max(1, $hours)),
        ]);

        $charge = $this->gateway->createCharge($transaction, $buyer);

        /** @var Transaction $transaction */
        $transaction = $this->transactions->update($transaction, [
            'snap_token' => $charge->token,
            'redirect_url' => $charge->redirectUrl,
        ]);

        return $transaction;
    }

    private function result(Transaction $transaction, float $percent, string $basis, bool $reused): array
    {
        return ['transaction' => $transaction, 'commission_percent' => $percent, 'fee_basis' => $basis, 'reused' => $reused];
    }

    private function commissionPercent(array $data): float
    {
        try {
            return ConciergeFees::resolveArchitectPercent(isset($data['commission_percent']) ? (float) $data['commission_percent'] : null);
        } catch (InvalidArgumentException $e) {
            throw new ApiException($e->getMessage(), 422);
        }
    }

    private function publishedPortfolio(int $id): ArchitectPortfolio
    {
        $portfolio = $this->portfolios->find($id);

        if (! $portfolio || $portfolio->status !== PortfolioStatus::Published || ! $portfolio->architect?->isActive()) {
            throw new ApiException('Portofolio tidak tersedia.', 422);
        }

        return $portfolio;
    }

    private function resolveBuyer(array $data): User
    {
        $buyer = isset($data['buyer_id'])
            ? $this->users->find((int) $data['buyer_id'])
            : $this->users->findByEmail((string) ($data['buyer_email'] ?? ''));

        if (! $buyer || ! $buyer->isActive() || ! $buyer->hasRole(RoleName::User->value)) {
            throw new ApiException('Akun pembeli tidak ditemukan. Minta pembeli mendaftar sebagai akun pembeli terlebih dahulu.', 422);
        }

        return $buyer;
    }

    private function resolveLead(?string $code): ?ConciergeLead
    {
        if ($code === null || $code === '') {
            return null;
        }

        return $this->leads->findByCode($code) ?? throw new ApiException('Kode prospek tidak ditemukan.', 404);
    }

    private function waMessage(Transaction $transaction, User $buyer): string
    {
        return implode("\n", [
            "Halo {$buyer->name}, berikut tautan pembayaran untuk:",
            $transaction->description,
            'Nominal: Rp '.number_format((float) $transaction->amount, 0, ',', '.'),
            'Berlaku hingga: '.$transaction->expires_at->format('d/m/Y H:i'),
            'Bayar di: '.$transaction->redirect_url,
            'Dapat dibayar via Virtual Account, kartu kredit (cicilan), atau e-wallet.',
            'Kode transaksi: '.$transaction->code,
        ]);
    }

    private function generateCode(): string
    {
        do {
            $code = 'TRX-'.now()->format('ymd').'-'.strtoupper(Str::random(8));
        } while ($this->transactions->findByCode($code) !== null);

        return $code;
    }
}
