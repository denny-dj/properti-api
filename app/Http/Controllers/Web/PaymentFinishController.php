<?php

namespace App\Http\Controllers\Web;

use App\Enums\TransactionStatus;
use App\Exceptions\ApiException;
use App\Http\Controllers\Controller;
use App\Repositories\Contracts\TransactionRepositoryInterface;
use App\Services\PaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Halaman tujuan setelah pembeli selesai di Midtrans. Status ditarik langsung dari Midtrans,
 * jadi tetap benar walau webhook belum terjangkau (mis. saat pengembangan di localhost).
 */
class PaymentFinishController extends Controller
{
    public function __invoke(Request $request, TransactionRepositoryInterface $transactions, PaymentService $payments): RedirectResponse
    {
        $transaction = $transactions->findByCode((string) $request->query('order_id', ''));

        if (! $transaction || $transaction->user_id !== $request->user()->id) {
            return redirect()->route('transactions.index')->with('status', 'Transaksi tidak ditemukan.');
        }

        try {
            $transaction = $payments->sync($transaction);
        } catch (ApiException) {
            // Biarkan: status akan diperbarui oleh webhook atau tombol "Cek status".
        }

        $message = $transaction->status === TransactionStatus::Paid
            ? 'Pembayaran berhasil. Terima kasih!'
            : 'Status pembayaran saat ini: '.$transaction->status->value.'.';

        return redirect()->route('transactions.show', $transaction->code)->with('status', $message);
    }
}
