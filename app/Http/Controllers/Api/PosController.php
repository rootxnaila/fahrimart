<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Barang;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PosController extends Controller
{
    public function produk(Request $request): JsonResponse
    {
        $produk = Barang::query()
            ->select(['id_barang', 'nama_barang', 'kategori', 'satuan', 'harga_jual', 'stok'])
            ->when($request->query('q'), fn ($q, $v) => $q->where('nama_barang', 'like', "%{$v}%"))
            ->when($request->query('kategori'), fn ($q, $v) => $q->where('kategori', $v))
            ->when($request->boolean('tersedia'), fn ($q) => $q->where('stok', '>', 0))
            ->orderBy('nama_barang')
            ->orderBy('id_barang')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Daftar produk',
            'data' => $produk,
            'meta' => ['total' => $produk->count()],
        ]);
    }
}