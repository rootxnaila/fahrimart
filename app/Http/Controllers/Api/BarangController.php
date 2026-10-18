<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBarangRequest;
use App\Http\Requests\UpdateBarangRequest;
use App\Models\Barang;
use App\Services\BarangService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BarangController extends Controller
{
    public function __construct(private BarangService $barangService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $perPage = min(max((int) $request->query('per_page', 10), 1), 100);

        $barang = Barang::query()
            ->when($request->query('q'), fn ($q, $v) => $q->where('nama_barang', 'like', "%{$v}%"))
            ->when($request->query('kategori'), fn ($q, $v) => $q->where('kategori', $v))
            ->orderBy('nama_barang')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Daftar barang',
            'data' => $barang->items(),
            'meta' => [
                'current_page' => $barang->currentPage(),
                'last_page' => $barang->lastPage(),
                'per_page' => $barang->perPage(),
                'total' => $barang->total(),
            ],
        ]);
    }

    public function show(Barang $barang): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Detail barang',
            'data' => $barang,
        ]);
    }

    public function store(StoreBarangRequest $request): JsonResponse
    {
        $barang = $this->barangService->tambah($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Barang berhasil ditambahkan',
            'data' => $barang,
        ], 201);
    }

    public function update(UpdateBarangRequest $request, Barang $barang): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Barang berhasil diubah',
            'data' => $this->barangService->ubah($barang, $request->validated()),
        ]);
    }

    public function destroy(Barang $barang): JsonResponse
    {
        if (! $this->barangService->hapus($barang)) {
            return response()->json([
                'success' => false,
                'message' => 'Barang tidak dapat dihapus karena sudah dipakai pada transaksi, pembelian stok, atau retur.',
            ], 409);
        }

        return response()->json([
            'success' => true,
            'message' => 'Barang berhasil dihapus',
        ]);
    }
}