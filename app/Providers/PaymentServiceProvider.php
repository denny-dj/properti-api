<?php

namespace App\Providers;

use App\Services\Payment\MidtransGateway;
use App\Services\Payment\PaymentGatewayInterface;
use Illuminate\Support\ServiceProvider;
use InvalidArgumentException;

class PaymentServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(PaymentGatewayInterface::class, function () {
            return match ($driver = config('payments.default')) {
                'midtrans' => new MidtransGateway(),
                // Untuk Xendit: buat App\Services\Payment\XenditGateway (implements PaymentGatewayInterface)
                // lalu tambahkan: 'xendit' => new XenditGateway(),
                default => throw new InvalidArgumentException("Gateway pembayaran tidak dikenal: {$driver}"),
            };
        });
    }
}
