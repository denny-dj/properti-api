<?php

use App\Http\Controllers\Api\V1\Admin\PartnerController as AdminPartnerController;
use App\Http\Controllers\Api\V1\Admin\PropertyController as AdminPropertyController;
use App\Http\Controllers\Api\V1\Admin\UserController as AdminUserController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\FacilityController;
use App\Http\Controllers\Api\V1\Partner\PropertyController as PartnerPropertyController;
use App\Http\Controllers\Api\V1\PropertyController;
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
    Route::get('properties/{slug}', [PropertyController::class, 'show']);
    Route::get('facilities', [FacilityController::class, 'index']);

    // ---------- Terautentikasi ----------
    Route::middleware(['auth:sanctum', 'active'])->group(function () {

        // Partner (Seller)
        Route::prefix('partner/properties')->middleware('role:partner')->group(function () {
            Route::get('/', [PartnerPropertyController::class, 'index']);
            Route::get('{property}', [PartnerPropertyController::class, 'show']);

            // Aksi tulis hanya untuk partner seller yang sudah diverifikasi admin
            Route::middleware('partner.approved:seller')->group(function () {
                Route::post('/', [PartnerPropertyController::class, 'store']);
                Route::put('{property}', [PartnerPropertyController::class, 'update']);
                Route::delete('{property}', [PartnerPropertyController::class, 'destroy']);
                Route::post('{property}/submit', [PartnerPropertyController::class, 'submit']);
                Route::post('{property}/images', [PartnerPropertyController::class, 'uploadImages']);

                Route::scopeBindings()->group(function () {
                    Route::delete('{property}/images/{image}', [PartnerPropertyController::class, 'destroyImage']);
                    Route::patch('{property}/images/{image}/cover', [PartnerPropertyController::class, 'setCover']);
                });
            });
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
        });
    });
});
