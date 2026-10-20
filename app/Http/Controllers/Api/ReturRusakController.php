<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreReturRequest;
use App\Models\ReturRusak;
use App\Services\ReturRusakService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReturRusakController extends Controller
{
    public function __construct(private ReturRusakService $returService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $perPage = min(max((int) $request->query('per_page', 10), 1), 100);

        $riwayat = ReturRusak::query()
            ->with('barang:id_barang,nama_barang,satuan')
            ->when($request->query('q'), fn ($q, $v) => $q->whereHas('barang', fn ($b) => $b->where('nama_barang', 'like', "%{$v}%")))
            ->when($request->query('jenis'), fn ($q, $v) => $q->where('jenis', $v))
            ->orderByDesc('tanggal_catat')
            ->orderByDesc('id_rusak')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Riwayat retur dan barang rusak',
            'data' => $riwayat->items(),
            'meta' => [
                'current_page' => $riwayat->currentPage(),
                'last_page' => $riwayat->lastPage(),
                'per_page' => $riwayat->perPage(),
                'total' => $riwayat->total(),
            ],
        ]);
    }

    public function show(ReturRusak $returRusak): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Detail retur atau barang rusak',
            'data' => $returRusak->load('barang'),
        ]);
    }

    public function store(StoreReturRequest $request): JsonResponse
    {
        $retur = $this->returService->catat($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Pencatatan berhasil',
            'data' => $retur,
            'stok_sekarang' => $retur->barang->stok,
        ], 201);
    }
}