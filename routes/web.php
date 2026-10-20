<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\BarangController;
use App\Http\Controllers\PembelianStokController;
use App\Http\Controllers\SupplierController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login');

// hanya untuk yang belum login
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->middleware('throttle:5,1');
});

// hanya untuk yang sudah login
Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
    Route::get('/dashboard', fn () => view('dashboard'))->name('dashboard');
});

// master data: hanya Pemilik dan Admin
Route::middleware(['auth', 'role:pemilik,admin'])
    ->prefix('master')
    ->name('master.')
    ->group(function () {
        Route::resource('barang', BarangController::class)
            ->only(['index', 'store', 'update', 'destroy']);

        Route::resource('supplier', SupplierController::class)
            ->only(['index', 'store', 'update', 'destroy']);
    });

// manajemen stok: hanya Pemilik dan Admin
Route::middleware(['auth', 'role:pemilik,admin'])
    ->prefix('stok')
    ->name('stok.')
    ->group(function () {
        Route::get('/pembelian', [PembelianStokController::class, 'index'])->name('pembelian.index');
        Route::post('/pembelian', [PembelianStokController::class, 'store'])->name('pembelian.store');
    });

Route::middleware(['auth', 'role:pemilik'])
    ->get('/uji/pemilik', fn () => 'Halaman khusus Pemilik')
    ->name('uji.pemilik');