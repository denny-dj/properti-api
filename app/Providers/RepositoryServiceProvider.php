<?php

namespace App\Providers;

use App\Repositories\Contracts\ArchitectServiceRepositoryInterface;
use App\Repositories\Contracts\AvailabilityRepositoryInterface;
use App\Repositories\Contracts\BookingRepositoryInterface;
use App\Repositories\Contracts\FacilityRepositoryInterface;
use App\Repositories\Contracts\PartnerProfileRepositoryInterface;
use App\Repositories\Contracts\PropertyImageRepositoryInterface;
use App\Repositories\Contracts\PropertyRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Repositories\Eloquent\ArchitectServiceRepository;
use App\Repositories\Eloquent\AvailabilityRepository;
use App\Repositories\Eloquent\BookingRepository;
use App\Repositories\Eloquent\FacilityRepository;
use App\Repositories\Eloquent\PartnerProfileRepository;
use App\Repositories\Eloquent\PropertyImageRepository;
use App\Repositories\Eloquent\PropertyRepository;
use App\Repositories\Eloquent\UserRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /** Interface => Implementasi. Tambahkan repository baru di sini. */
    public array $bindings = [
        UserRepositoryInterface::class => UserRepository::class,
        PartnerProfileRepositoryInterface::class => PartnerProfileRepository::class,
        PropertyRepositoryInterface::class => PropertyRepository::class,
        PropertyImageRepositoryInterface::class => PropertyImageRepository::class,
        FacilityRepositoryInterface::class => FacilityRepository::class,
        ArchitectServiceRepositoryInterface::class => ArchitectServiceRepository::class,
        AvailabilityRepositoryInterface::class => AvailabilityRepository::class,
        BookingRepositoryInterface::class => BookingRepository::class,
    ];
}
