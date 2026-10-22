<?php

namespace App\Services\Payment;

use App\Models\Transaction;
use App\Models\User;

interface PaymentGatewayInterface
{
    public function name(): string;

    /** Membuat tagihan (halaman pembayaran) untuk transaksi. */
    public function createCharge(Transaction $transaction, User $payer): ChargeResult;

    /** Memverifikasi keaslian webhook lalu menormalisasinya. Melempar ApiException(403) bila palsu. */
    public function parseNotification(array $payload): GatewayNotification;

    /** Menarik status terbaru langsung dari gateway (rekonsiliasi bila webhook terlewat). */
    public function fetchStatus(string $orderId): GatewayNotification;
}
