<?php

use App\Http\Controllers\Web\ArchitectPageController;
use App\Http\Controllers\Web\ChatPageController;
use App\Http\Controllers\Web\HomeController;
use App\Http\Controllers\Web\LogoutController;
use App\Http\Controllers\Web\OrderPageController;
use App\Http\Controllers\Web\PaymentFinishController;
use App\Http\Controllers\Web\PropertyPageController;
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
});

Route::post('/keluar', LogoutController::class)->middleware('auth')->name('logout');

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
