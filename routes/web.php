<?php

use App\Http\Controllers\Web\ArchitectPageController;
use App\Http\Controllers\Web\ChatPageController;
use App\Http\Controllers\Web\HomeController;
use App\Http\Controllers\Web\LogoutController;
use App\Http\Controllers\Web\OrderPageController;
use App\Http\Controllers\Web\PaymentFinishController;
use App\Http\Controllers\Web\Partner\PropertyPageController as PartnerPropertyPageController;
use App\Http\Controllers\Web\PropertyPageController;
use App\Http\Controllers\Web\VerifyEmailController;
use App\Http\Middleware\EnsurePartnerApprovedWeb;
use Illuminate\Support\Facades\Route;

// ---------- Publik ----------
Route::get('/', [HomeController::class, 'index'])->name('home');

Route::view('/properti', 'pages.properties')->name('properties.index');
Route::get('/properti/{slug}', [PropertyPageController::class, 'show'])->name('properties.show');
Route::view('/peta', 'pages.map')->name('map');

Route::view('/arsitek', 'pages.architects')->name('architects.index');
Route::get('/arsitek/{slug}', [ArchitectPageController::class, 'show'])->name('architects.show');

Route::view('/kpr', 'pages.kpr')->name('kpr');

// ---------- Autentikasi (sesi/cookie) ----------
Route::middleware('guest')->group(function () {
    Route::view('/masuk', 'pages.login')->name('login');
    Route::view('/daftar', 'pages.register')->name('register');
    Route::view('/daftar-mitra', 'pages.register-partner')->name('register.partner');
    Route::view('/lupa-password', 'pages.forgot-password')->name('password.request');
    Route::view('/reset-password/{token}', 'pages.reset-password')->name('password.reset');
});

Route::post('/keluar', LogoutController::class)->middleware('auth')->name('logout');

// ---------- Verifikasi email ----------
Route::middleware('auth')->group(function () {
    Route::view('/verifikasi-email', 'pages.verify-notice')->name('verification.notice');
    Route::get('/verifikasi-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');
});

// ---------- Area akun (wajib login) ----------
Route::middleware('auth')->group(function () {

    // Chat: buyer & partner
    Route::middleware('role:user|partner')->group(function () {
        Route::view('/chat', 'pages.chat-inbox')->name('chat.index');
        Route::get('/chat/{conversation}', [ChatPageController::class, 'show'])->name('chat.room');
    });

    // Pembeli: pesanan konsultasi, transaksi, pembayaran
    Route::middleware('role:user')->group(function () {
        Route::view('/pesanan', 'pages.orders')->name('orders.index');
        Route::get('/pesanan/{booking}', [OrderPageController::class, 'booking'])->name('orders.show');

        Route::view('/transaksi', 'pages.transactions')->name('transactions.index');
        Route::get('/transaksi/{transaction}', [OrderPageController::class, 'transaction'])->name('transactions.show');

        // Tujuan redirect Midtrans setelah pembayaran (lihat PAYMENT_FINISH_URL)
        Route::get('/pembayaran/selesai', PaymentFinishController::class)->name('payments.finish');
    });
});

// ---------- Dashboard mitra (seller & arsitek) ----------
Route::middleware(['auth', 'role:partner'])->prefix('mitra')->name('partner.')->group(function () {
    Route::view('/', 'pages.partner.dashboard')->name('dashboard');
    Route::view('/transaksi', 'pages.partner.transactions')->name('transactions');

    // Khusus seller yang sudah diverifikasi
    Route::middleware(EnsurePartnerApprovedWeb::class.':seller')->group(function () {
        Route::view('/properti', 'pages.partner.properties')->name('properties.index');
        Route::view('/properti/baru', 'pages.partner.property-create')->name('properties.create');
        Route::get('/properti/{property}/ubah', [PartnerPropertyPageController::class, 'edit'])->name('properties.edit');
    });

    // Khusus arsitek yang sudah diverifikasi
    Route::middleware(EnsurePartnerApprovedWeb::class.':architect')->group(function () {
        Route::view('/layanan', 'pages.partner.services')->name('services');
        Route::view('/jadwal', 'pages.partner.availability')->name('availability');
        Route::view('/pesanan', 'pages.partner.bookings')->name('bookings');
    });
});

// ---------- Dashboard admin ----------
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::view('/', 'pages.admin.dashboard')->name('dashboard');

    Route::view('/mitra', 'pages.admin.partners')->middleware('permission:partners.verify')->name('partners');
    Route::view('/properti', 'pages.admin.properties')->middleware('permission:properties.verify')->name('properties');
    Route::view('/pengguna', 'pages.admin.users')->middleware('permission:users.manage')->name('users');
    Route::view('/pesanan', 'pages.admin.bookings')->middleware('permission:bookings.view')->name('bookings');
    Route::view('/transaksi', 'pages.admin.transactions')->middleware('permission:transactions.view')->name('transactions');
});
