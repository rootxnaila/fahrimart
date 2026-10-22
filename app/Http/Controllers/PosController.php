<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTransaksiRequest;
use App\Models\Barang;
use App\Services\TransaksiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class PosController extends Controller
{
    public function __construct(private TransaksiService $transaksiService)
    {
    }

    public function index()
    {
        $produk = Barang::query()
            ->orderBy('nama_barang')
            ->orderBy('id_barang')
            ->get(['id_barang', 'nama_barang', 'kategori', 'satuan', 'harga_jual', 'stok']);

        $kategori = $produk->pluck('kategori')->unique()->sort()->values();

        // 4 barang terlaris (berdasarkan jumlah terjual) yang stoknya masih ada
        $populerIds = DB::table('detail_transaksi as d')
            ->join('barang as b', 'b.id_barang', '=', 'd.id_barang')
            ->where('b.stok', '>', 0)
            ->groupBy('b.id_barang')
            ->orderByRaw('SUM(d.qty) DESC')
            ->orderBy('b.id_barang')
            ->limit(4)
            ->select('b.id_barang')
            ->get()
            ->pluck('id_barang')
            ->all();

        $populer = $produk
            ->whereIn('id_barang', $populerIds)
            ->sortBy(fn ($p) => array_search($p->id_barang, $populerIds))
            ->values();

        // Data untuk JavaScript keranjang
        $produkJs = $produk->mapWithKeys(fn ($p) => [
            $p->id_barang => [
                'nama' => $p->nama_barang,
                'satuan' => $p->satuan,
                'harga' => (float) $p->harga_jual,
                'stok' => (int) $p->stok,
            ],
        ]);

        return view('pos.index', [
            'produk' => $produk,
            'populer' => $populer,
            'kategori' => $kategori,
            'produkJs' => $produkJs,
            'ppnPersen' => config('fahrimart.ppn_persen'),
            'toko' => [
                'nama' => config('fahrimart.toko_nama'),
                'alamat' => config('fahrimart.toko_alamat'),
            ],
        ]);
    }

    public function store(StoreTransaksiRequest $request): JsonResponse
    {
        $struk = $this->transaksiService->proses($request->user(), $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Transaksi berhasil',
            'data' => $struk,
        ], 201);
    }
}