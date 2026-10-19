<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SupplierRequest;
use App\Models\Supplier;
use App\Services\SupplierService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function __construct(private SupplierService $supplierService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $perPage = min(max((int) $request->query('per_page', 10), 1), 100);

        $supplier = Supplier::query()
            ->when($request->query('q'), fn ($q, $v) => $q->where('nama_supplier', 'like', "%{$v}%"))
            ->orderBy('nama_supplier')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Daftar supplier',
            'data' => $supplier->items(),
            'meta' => [
                'current_page' => $supplier->currentPage(),
                'last_page' => $supplier->lastPage(),
                'per_page' => $supplier->perPage(),
                'total' => $supplier->total(),
            ],
        ]);
    }

    public function show(Supplier $supplier): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Detail supplier',
            'data' => $supplier,
        ]);
    }

    public function store(SupplierRequest $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Supplier berhasil ditambahkan',
            'data' => $this->supplierService->tambah($request->validated()),
        ], 201);
    }

    public function update(SupplierRequest $request, Supplier $supplier): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Supplier berhasil diubah',
            'data' => $this->supplierService->ubah($supplier, $request->validated()),
        ]);
    }

    public function destroy(Supplier $supplier): JsonResponse
    {
        if (! $this->supplierService->hapus($supplier)) {
            return response()->json([
                'success' => false,
                'message' => 'Supplier tidak dapat dihapus karena sudah dipakai pada pembelian stok.',
            ], 409);
        }

        return response()->json([
            'success' => true,
            'message' => 'Supplier berhasil dihapus',
        ]);
    }
}