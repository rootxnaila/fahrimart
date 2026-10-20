<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePembelianRequest;
use App\Models\PembelianStok;
use App\Services\PembelianStokService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PembelianStokController extends Controller
{
    public function __construct(private PembelianStokService $pembelianService)
    {
    }

    /**
     * Riwayat pembelian. Parameter opsional: q (nama barang), id_supplier, per_page.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = min(max((int) $request->query('per_page', 10), 1), 100);

        $riwayat = PembelianStok::query()
            ->with(['supplier:id_supplier,nama_supplier', 'barang:id_barang,nama_barang,satuan'])
            ->when($request->query('q'), fn ($q, $v) => $q->whereHas('barang', fn ($b) => $b->where('nama_barang', 'like', "%{$v}%")))
            ->when($request->query('id_supplier'), fn ($q, $v) => $q->where('id_supplier', $v))
            ->orderByDesc('tanggal_beli')
            ->orderByDesc('id_pembelian')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Riwayat pembelian stok',
            'data' => $riwayat->items(),
            'meta' => [
                'current_page' => $riwayat->currentPage(),
                'last_page' => $riwayat->lastPage(),
                'per_page' => $riwayat->perPage(),
                'total' => $riwayat->total(),
            ],
        ]);
    }

    public function show(PembelianStok $pembelianStok): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Detail pembelian stok',
            'data' => $pembelianStok->load(['supplier', 'barang']),
        ]);
    }

    public function store(StorePembelianRequest $request): JsonResponse
    {
        $pembelian = $this->pembelianService->catat($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Pembelian stok berhasil dicatat',
            'data' => $pembelian,
            'stok_sekarang' => $pembelian->barang->stok,
        ], 201);
    }
}