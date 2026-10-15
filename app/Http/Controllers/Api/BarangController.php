<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Barang;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BarangController extends Controller
{
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

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate($this->rules() + [
            'stok' => ['required', 'integer', 'min:0'],
        ]);

        DB::statement('CALL add_barang(?, ?, ?, ?, ?, ?)', [
            $data['nama_barang'],
            $data['kategori'],
            $data['satuan'],
            $data['harga_beli'],
            $data['harga_jual'],
            $data['stok'],
        ]);

        $id = DB::selectOne('SELECT LAST_INSERT_ID() AS id')->id;

        return response()->json([
            'success' => true,
            'message' => 'Barang berhasil ditambahkan',
            'data' => Barang::findOrFail($id),
        ], 201);
    }


    public function update(Request $request, Barang $barang): JsonResponse
    {
        $data = $request->validate($this->rules());

        DB::statement('CALL update_barang(?, ?, ?, ?, ?, ?)', [
            $barang->id_barang,
            $data['nama_barang'],
            $data['kategori'],
            $data['satuan'],
            $data['harga_beli'],
            $data['harga_jual'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Barang berhasil diubah',
            'data' => $barang->fresh(),
        ]);
    }

    public function destroy(Barang $barang): JsonResponse
    {
        try {
            DB::statement('CALL delete_barang(?)', [$barang->id_barang]);
        } catch (QueryException $e) {
            // 23000 = pelanggaran foreign key (barang sudah dipakai di transaksi, pembelian, atau retur)
            if ($e->getCode() === '23000') {
                return response()->json([
                    'success' => false,
                    'message' => 'Barang tidak dapat dihapus karena sudah dipakai pada transaksi, pembelian stok, atau retur.',
                ], 409);
            }

            throw $e;
        }

        return response()->json([
            'success' => true,
            'message' => 'Barang berhasil dihapus',
        ]);
    }

    private function rules(): array
    {
        return [
            'nama_barang' => ['required', 'string', 'max:100'],
            'kategori' => ['required', 'string', 'max:50'],
            'satuan' => ['required', 'string', 'max:20'],
            'harga_beli' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'harga_jual' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
        ];
    }
}