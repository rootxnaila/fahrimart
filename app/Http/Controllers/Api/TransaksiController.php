<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTransaksiRequest;
use App\Models\Transaksi;
use App\Services\TransaksiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TransaksiController extends Controller
{
    public function __construct(private TransaksiService $transaksiService)
    {
    }

    /**
     * Proses transaksi penjualan multi-item.
     */
    public function store(StoreTransaksiRequest $request): JsonResponse
    {
        $struk = $this->transaksiService->proses($request->user(), $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Transaksi berhasil',
            'data' => $struk,
        ], 201);
    }

    /**
     * Struk satu transaksi. Kasir hanya boleh melihat transaksinya sendiri.
     */
    public function show(Request $request, Transaksi $transaksi): JsonResponse
    {
        $user = $request->user();

        abort_if(
            $user->role === 'kasir' && (int) $transaksi->id_user !== (int) $user->id_user,
            403,
            'Anda hanya dapat melihat transaksi Anda sendiri.'
        );

        return response()->json([
            'success' => true,
            'message' => 'Struk transaksi',
            'data' => $this->transaksiService->struk($transaksi->id_transaksi),
        ]);
    }
}