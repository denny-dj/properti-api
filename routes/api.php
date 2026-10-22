<?php

use App\Http\Controllers\Api\V1\Admin\BookingController as AdminBookingController;
use App\Http\Controllers\Api\V1\Admin\PartnerController as AdminPartnerController;
use App\Http\Controllers\Api\V1\Admin\PropertyController as AdminPropertyController;
use App\Http\Controllers\Api\V1\Admin\TransactionController as AdminTransactionController;
use App\Http\Controllers\Api\V1\Admin\UserController as AdminUserController;
use App\Http\Controllers\Api\V1\ArchitectServiceController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\BookingController;
use App\Http\Controllers\Api\V1\ConversationController;
use App\Http\Controllers\Api\V1\FacilityController;
use App\Http\Controllers\Api\V1\KprController;
use App\Http\Controllers\Api\V1\MapController;
use App\Http\Controllers\Api\V1\MessageController;
use App\Http\Controllers\Api\V1\Partner\ArchitectServiceController as PartnerArchitectServiceController;
use App\Http\Controllers\Api\V1\Partner\AvailabilityController;
use App\Http\Controllers\Api\V1\Partner\BookingController as PartnerBookingController;
use App\Http\Controllers\Api\V1\Partner\PropertyController as PartnerPropertyController;
use App\Http\Controllers\Api\V1\Partner\PropertySaleController;
use App\Http\Controllers\Api\V1\Partner\TransactionController as PartnerTransactionController;
use App\Http\Controllers\Api\V1\PaymentController;
use App\Http\Controllers\Api\V1\PropertyController;
use App\Http\Controllers\Api\V1\TransactionController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {

    // ---------- Auth ----------
    Route::prefix('auth')->group(function () {
        Route::post('register', [AuthController::class, 'register'])->middleware('throttle:10,1');
        Route::post('register-partner', [AuthController::class, 'registerPartner'])->middleware('throttle:10,1');
        Route::post('login', [AuthController::class, 'login'])->middleware('throttle:5,1');

        Route::middleware(['auth:sanctum', 'active'])->group(function () {
            Route::post('logout', [AuthController::class, 'logout']);
            Route::get('me', [AuthController::class, 'me']);
        });
    });

    // ---------- Publik ----------
    Route::get('properties', [PropertyController::class, 'index']);
    Route::get('properties/markers', [MapController::class, 'markers']);   // harus sebelum {slug}
    Route::get('properties/{slug}', [PropertyController::class, 'show']);
    Route::get('properties/{slug}/kpr', [KprController::class, 'forProperty']);
    Route::get('facilities', [FacilityController::class, 'index']);

    Route::post('kpr/simulate', [KprController::class, 'simulate'])->middleware('throttle:60,1');

    Route::get('architect-services', [ArchitectServiceController::class, 'index']);
    Route::get('architect-services/{slug}', [ArchitectServiceController::class, 'show']);
    Route::get('architect-services/{slug}/slots', [ArchitectServiceController::class, 'slots']);

    // Webhook payment gateway (publik; keaslian diverifikasi lewat signature)
    Route::post('payments/notification', [PaymentController::class, 'notification']);

    // ---------- Terautentikasi ----------
    Route::middleware(['auth:sanctum', 'active'])->group(function () {

        // Peta (berbayar per request -> wajib login + throttle)
        Route::prefix('maps')->middleware('throttle:30,1')->group(function () {
            Route::get('geocode', [MapController::class, 'geocode']);
            Route::get('reverse-geocode', [MapController::class, 'reverseGeocode']);
        });

        // Chat: buyer & partner (admin tidak ikut membaca chat privat)
        Route::middleware('role:user|partner')->group(function () {
            Route::get('conversations', [ConversationController::class, 'index']);
            Route::post('conversations', [ConversationController::class, 'store'])->middleware('role:user');
            Route::get('conversations/{conversation}/messages', [MessageController::class, 'index']);
            Route::post('conversations/{conversation}/messages', [MessageController::class, 'store'])->middleware('throttle:30,1');
            Route::post('conversations/{conversation}/read', [ConversationController::class, 'read']);
        });

        // Detail & sinkronisasi transaksi (akses dicek oleh TransactionPolicy)
        Route::get('transactions/{transaction}', [TransactionController::class, 'show']);
        Route::post('transactions/{transaction}/sync', [TransactionController::class, 'sync'])->middleware('throttle:20,1');

        // Buyer / klien
        Route::middleware('role:user')->group(function () {
            Route::get('bookings', [BookingController::class, 'index']);
            Route::post('bookings', [BookingController::class, 'store'])->middleware('throttle:20,1');
            Route::get('bookings/{booking}', [BookingController::class, 'show']);
            Route::post('bookings/{booking}/cancel', [BookingController::class, 'cancel']);

            Route::post('payments/property-booking-fee', [PaymentController::class, 'propertyBookingFee'])->middleware('throttle:10,1');
            Route::post('payments/architect-bookings', [PaymentController::class, 'architectBooking'])->middleware('throttle:10,1');
            Route::get('transactions', [TransactionController::class, 'index']);
        });

        // Partner (Seller): listing properti
        Route::prefix('partner/properties')->middleware('role:partner')->group(function () {
            Route::get('/', [PartnerPropertyController::class, 'index']);
            Route::get('{property}', [PartnerPropertyController::class, 'show']);

            Route::middleware('partner.approved:seller')->group(function () {
                Route::post('/', [PartnerPropertyController::class, 'store']);
                Route::put('{property}', [PartnerPropertyController::class, 'update']);
                Route::delete('{property}', [PartnerPropertyController::class, 'destroy']);
                Route::post('{property}/submit', [PartnerPropertyController::class, 'submit']);
                Route::post('{property}/images', [PartnerPropertyController::class, 'uploadImages']);
                Route::post('{property}/mark-sold', [PropertySaleController::class, 'markSold']);
                Route::post('{property}/release', [PropertySaleController::class, 'release']);

                Route::scopeBindings()->group(function () {
                    Route::delete('{property}/images/{image}', [PartnerPropertyController::class, 'destroyImage']);
                    Route::patch('{property}/images/{image}/cover', [PartnerPropertyController::class, 'setCover']);
                });
            });
        });

        // Partner (semua tipe): pembayaran masuk
        Route::get('partner/transactions', [PartnerTransactionController::class, 'index'])->middleware('role:partner');

        // Partner (Architect): layanan, jadwal, pesanan masuk
        Route::prefix('partner')->middleware(['role:partner', 'partner.approved:architect'])->group(function () {
            Route::get('availabilities', [AvailabilityController::class, 'show']);
            Route::put('availabilities', [AvailabilityController::class, 'update']);

            Route::apiResource('architect-services', PartnerArchitectServiceController::class)
                ->parameters(['architect-services' => 'architectService']);

            Route::get('bookings', [PartnerBookingController::class, 'index']);
            Route::get('bookings/{booking}', [PartnerBookingController::class, 'show']);
            Route::post('bookings/{booking}/confirm', [PartnerBookingController::class, 'confirm']);
            Route::post('bookings/{booking}/reject', [PartnerBookingController::class, 'reject']);
            Route::post('bookings/{booking}/complete', [PartnerBookingController::class, 'complete']);
            Route::post('bookings/{booking}/cancel', [PartnerBookingController::class, 'cancel']);
        });

        // Administrator
        Route::prefix('admin')->middleware('role:admin')->group(function () {
            Route::middleware('permission:partners.verify')->group(function () {
                Route::get('partners', [AdminPartnerController::class, 'index']);
                Route::post('partners/{partnerProfile}/approve', [AdminPartnerController::class, 'approve']);
                Route::post('partners/{partnerProfile}/reject', [AdminPartnerController::class, 'reject']);
            });

            Route::middleware('permission:properties.verify')->group(function () {
                Route::get('properties', [AdminPropertyController::class, 'index']);
                Route::get('properties/{property}', [AdminPropertyController::class, 'show']);
                Route::post('properties/{property}/approve', [AdminPropertyController::class, 'approve']);
                Route::post('properties/{property}/reject', [AdminPropertyController::class, 'reject']);
            });

            Route::middleware('permission:users.manage')->group(function () {
                Route::get('users', [AdminUserController::class, 'index']);
                Route::patch('users/{user}/status', [AdminUserController::class, 'updateStatus']);
            });

            Route::middleware('permission:bookings.view')->group(function () {
                Route::get('bookings', [AdminBookingController::class, 'index']);
                Route::get('bookings/{booking}', [AdminBookingController::class, 'show']);
            });

            Route::middleware('permission:transactions.view')->group(function () {
                Route::get('transactions', [AdminTransactionController::class, 'index']);
                Route::get('transactions/summary', [AdminTransactionController::class, 'summary']); // sebelum {transaction}
                Route::get('transactions/{transaction}', [AdminTransactionController::class, 'show']);
            });
        });
    });
});
