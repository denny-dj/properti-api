<?php

use App\Http\Controllers\Web\ArchitectPageController;
use App\Http\Controllers\Web\HomeController;
use App\Http\Controllers\Web\LogoutController;
use App\Http\Controllers\Web\PropertyPageController;
use App\Livewire\Auth\Login;
use App\Livewire\Auth\Register;
use App\Livewire\Auth\RegisterPartner;
use App\Livewire\Pages\ArchitectIndex;
use App\Livewire\Pages\PropertyIndex;
use Illuminate\Support\Facades\Route;

// ---------- Publik ----------
Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/properti', PropertyIndex::class)->name('properties.index');
Route::get('/properti/{slug}', [PropertyPageController::class, 'show'])->name('properties.show');
Route::view('/peta', 'pages.map')->name('map');

Route::get('/arsitek', ArchitectIndex::class)->name('architects.index');
Route::get('/arsitek/{slug}', [ArchitectPageController::class, 'show'])->name('architects.show');

Route::view('/kpr', 'pages.kpr')->name('kpr');

// ---------- Autentikasi (sesi/cookie) ----------
Route::middleware('guest')->group(function () {
    Route::get('/masuk', Login::class)->name('login');
    Route::get('/daftar', Register::class)->name('register');
    Route::get('/daftar-mitra', RegisterPartner::class)->name('register.partner');
});

Route::post('/keluar', LogoutController::class)->middleware('auth')->name('logout');
