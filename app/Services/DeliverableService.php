<?php

namespace App\Services;

use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Exceptions\ApiException;
use App\Models\Transaction;
use App\Models\User;
use App\Repositories\Contracts\TransactionRepositoryInterface;
use App\Support\ConciergeNotices;
use App\Support\DeliverableUrl;
use App\Support\Notifier;
use Illuminate\Support\Facades\DB;

/**
 * Serah terima berkas desain lewat tautan cloud (hemat storage).
 * Dana mitra dianggap "dilepas" saat pembeli menekan Konfirmasi Penerimaan Berkas.
 */
class DeliverableService
{
    public function __construct(private TransactionRepositoryInterface $transactions)
    {
    }

    /** Mitra mengirim/mengganti tautan berkas (selama belum dikonfirmasi pembeli). */
    public function submit(Transaction $transaction, User $partner, string $link): Transaction
    {
        $link = trim($link);

        if (! DeliverableUrl::isAllowed($link)) {
            throw new ApiException('Tautan harus https ke Google Drive (host yang diizinkan).', 422);
        }

        $updated = DB::transaction(function () use ($transaction, $partner, $link) {
            $locked = $this->lock($transaction);

            if ($locked->payee_id !== $partner->id) {
                throw new ApiException('Transaksi ini bukan milik Anda.', 403);
            }

            $this->assertDeliverableType($locked);

            if ($locked->status !== TransactionStatus::Paid) {
                throw new ApiException('Pembayaran belum diterima. Berkas baru dapat dikirim setelah pembeli membayar.', 422);
            }

            if ($locked->client_confirmed_at !== null) {
                throw new ApiException('Pembeli sudah mengonfirmasi penerimaan berkas.', 422);
            }

            /** @var Transaction $locked */
            $locked = $this->transactions->update($locked, [
                'deliverable_link' => $link,
                'deliverable_submitted_at' => now(),
            ]);

            return $locked;
        });

        Notifier::safely(fn () => Notifier::send($updated->payer, ConciergeNotices::deliverableSubmitted($updated)));

        return $updated;
    }

    /** Pembeli mengonfirmasi berkas diterima dan sesuai -> dana mitra dilepas. */
    public function confirm(Transaction $transaction, User $buyer): Transaction
    {
        $updated = DB::transaction(function () use ($transaction, $buyer) {
            $locked = $this->lock($transaction);

            if ($locked->user_id !== $buyer->id) {
                throw new ApiException('Transaksi ini bukan milik Anda.', 403);
            }

            $this->assertDeliverableType($locked);

            if ($locked->status !== TransactionStatus::Paid) {
                throw new ApiException('Pembayaran belum lunas.', 422);
            }

            if ($locked->deliverable_link === null) {
                throw new ApiException('Arsitek belum mengirim tautan berkas.', 422);
            }

            if ($locked->client_confirmed_at !== null) {
                throw new ApiException('Penerimaan berkas sudah dikonfirmasi sebelumnya.', 422);
            }

            /** @var Transaction $locked */
            $locked = $this->transactions->update($locked, ['client_confirmed_at' => now()]);

            return $locked;
        });

        Notifier::safely(fn () => Notifier::send($updated->payee, ConciergeNotices::deliverableConfirmed($updated)));

        return $updated;
    }

    private function lock(Transaction $transaction): Transaction
    {
        return $this->transactions->findByCodeForUpdate($transaction->code)
            ?? throw new ApiException('Transaksi tidak ditemukan.', 404);
    }

    private function assertDeliverableType(Transaction $transaction): void
    {
        if (! in_array($transaction->type, [TransactionType::ArchitectBlueprint, TransactionType::ArchitectCustomBooking], true)) {
            throw new ApiException('Transaksi ini tidak memiliki serah terima berkas.', 422);
        }
    }
}
