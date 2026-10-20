<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BarangController;
use App\Http\Controllers\Api\PembelianStokController;
use App\Http\Controllers\Api\PosController;
use App\Http\Controllers\Api\ReturRusakController;
use App\Http\Controllers\Api\SupplierController;
use App\Http\Controllers\Api\TransaksiController;
use Illuminate\Support\Facades\Route;

// Publik
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');

// WAJIB membawa token Bearer
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Operasional kasir: Pemilik, Admin, dan Kasir
    Route::middleware('role:pemilik,admin,kasir')->group(function () {
        Route::get('/pos/produk', [PosController::class, 'produk']);
        Route::post('/transaksi', [TransaksiController::class, 'store']);
        Route::get('/transaksi/{transaksi}', [TransaksiController::class, 'show']);
    });

    // Master data dan manajemen stok: hanya Pemilik dan Admin
    Route::middleware('role:pemilik,admin')->group(function () {
        Route::apiResource('barang', BarangController::class);
        Route::apiResource('supplier', SupplierController::class);
        Route::apiResource('pembelian-stok', PembelianStokController::class)
            ->only(['index', 'store', 'show']);
        Route::apiResource('retur-rusak', ReturRusakController::class)
            ->only(['index', 'store', 'show']);
    });
});