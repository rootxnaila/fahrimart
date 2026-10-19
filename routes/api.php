<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BarangController;
use App\Http\Controllers\Api\SupplierController;
use Illuminate\Support\Facades\Route;

// Publik
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');

// WAJIB token Bearer
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Master data: hanya Pemilik dan Admin
    Route::middleware('role:pemilik,admin')->group(function () {
        Route::apiResource('barang', BarangController::class);
        Route::apiResource('supplier', SupplierController::class);
    });
});