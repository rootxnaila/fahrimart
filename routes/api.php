<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BarangController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // master data: hanya Pemilik dan Admin
    Route::middleware('role:pemilik,admin')->group(function () {
        Route::apiResource('barang', BarangController::class);
    });
});