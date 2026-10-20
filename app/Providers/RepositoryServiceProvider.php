<?php

namespace App\Providers;

use App\Repositories\Contracts\PropertyRepositoryInterface;
use App\Repositories\Eloquent\PropertyRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /** Interface => Implementasi. Tambahkan repository baru di sini. */
    public array $bindings = [
        PropertyRepositoryInterface::class => PropertyRepository::class,
    ];
}
