<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\BarangController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\PembelianStokController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\ReturRusakController;
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
});

// kasir (dashboard POS): Pemilik, Admin, dan Kasir
Route::middleware(['auth', 'role:pemilik,admin,kasir'])->group(function () {
    Route::get('/dashboard', [PosController::class, 'index'])->name('dashboard');
    Route::post('/pos/transaksi', [PosController::class, 'store'])->name('pos.transaksi.store');
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

        Route::get('/retur', [ReturRusakController::class, 'index'])->name('retur.index');
        Route::post('/retur', [ReturRusakController::class, 'store'])->name('retur.store');
    });

// laporan penjualan + analisis AI: hanya Pemilik dan Admin
Route::middleware(['auth', 'role:pemilik,admin'])
    ->prefix('laporan')
    ->name('laporan.')
    ->group(function () {
        Route::get('/', [LaporanController::class, 'index'])->name('index');
        Route::post('/analisis', [LaporanController::class, 'perbarui'])->name('analisis.perbarui');
    });
Route::middleware(['auth', 'role:pemilik'])
    ->get('/uji/pemilik', fn () => 'Halaman khusus Pemilik')
    ->name('uji.pemilik');