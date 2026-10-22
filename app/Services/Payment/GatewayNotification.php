<?php

namespace App\Services\Payment;

use App\Enums\TransactionStatus;

/** Status pembayaran yang sudah dinormalisasi dari gateway apa pun. */
final class GatewayNotification
{
    public function __construct(
        public readonly string $orderId,
        public readonly TransactionStatus $status,
        public readonly float $grossAmount,
        public readonly ?string $paymentType = null,
        public readonly ?string $channel = null,
        public readonly ?string $gatewayTransactionId = null,
        public readonly ?string $fraudStatus = null,
        public readonly ?int $installmentTerm = null,
        public readonly bool $needsReview = false,
        public readonly array $raw = [],
    ) {
    }
}
