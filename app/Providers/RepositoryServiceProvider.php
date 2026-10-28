<?php

namespace App\Providers;

use App\Repositories\Contracts\ArchitectServiceRepositoryInterface;
use App\Repositories\Contracts\AvailabilityRepositoryInterface;
use App\Repositories\Contracts\BookingRepositoryInterface;
use App\Repositories\Contracts\ConversationRepositoryInterface;
use App\Repositories\Contracts\FacilityRepositoryInterface;
use App\Repositories\Contracts\MessageRepositoryInterface;
use App\Repositories\Contracts\PartnerProfileRepositoryInterface;
use App\Repositories\Contracts\PropertyImageRepositoryInterface;
use App\Repositories\Contracts\PropertyRepositoryInterface;
use App\Repositories\Contracts\TransactionRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Repositories\Eloquent\ArchitectServiceRepository;
use App\Repositories\Eloquent\AvailabilityRepository;
use App\Repositories\Eloquent\BookingRepository;
use App\Repositories\Eloquent\ConversationRepository;
use App\Repositories\Eloquent\FacilityRepository;
use App\Repositories\Eloquent\MessageRepository;
use App\Repositories\Eloquent\PartnerProfileRepository;
use App\Repositories\Eloquent\PropertyImageRepository;
use App\Repositories\Eloquent\PropertyRepository;
use App\Repositories\Eloquent\TransactionRepository;
use App\Repositories\Eloquent\UserRepository;
use App\Models\ArchitectBooking;
use App\Models\PartnerProfile;
use App\Models\Property;
use App\Models\Transaction;
use App\Observers\ArchitectBookingObserver;
use App\Observers\PartnerProfileObserver;
use App\Observers\PropertyObserver;
use App\Observers\TransactionObserver;
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
        ConversationRepositoryInterface::class => ConversationRepository::class,
        MessageRepositoryInterface::class => MessageRepository::class,
        TransactionRepositoryInterface::class => TransactionRepository::class,
    ];

    /** Observer: kirim notifikasi email saat status penting berubah (dari API, web, maupun admin). */
    public function boot(): void
    {
        PartnerProfile::observe(PartnerProfileObserver::class);
        Property::observe(PropertyObserver::class);
        ArchitectBooking::observe(ArchitectBookingObserver::class);
        Transaction::observe(TransactionObserver::class);
    }
}
