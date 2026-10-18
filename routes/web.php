<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\BarangController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login');

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->middleware('throttle:5,1');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
    Route::get('/dashboard', fn () => view('dashboard'))->name('dashboard');
});

Route::middleware(['auth', 'role:pemilik,admin'])
    ->prefix('master')
    ->name('master.')
    ->group(function () {
        Route::resource('barang', BarangController::class)
            ->only(['index', 'store', 'update', 'destroy']);
    });

Route::middleware(['auth', 'role:pemilik'])
    ->get('/uji/pemilik', fn () => 'Halaman khusus Pemilik')
    ->name('uji.pemilik');