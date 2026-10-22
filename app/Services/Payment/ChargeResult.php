<?php

namespace App\Services\Payment;

/** Hasil pembuatan tagihan di gateway. */
final class ChargeResult
{
    public function __construct(
        public readonly string $token,
        public readonly string $redirectUrl,
        public readonly array $raw = [],
    ) {
    }
}
