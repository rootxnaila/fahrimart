<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePembelianRequest;
use App\Models\Barang;
use App\Models\PembelianStok;
use App\Models\Supplier;
use App\Services\PembelianStokService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PembelianStokController extends Controller
{
    public function __construct(private PembelianStokService $pembelianService)
    {
    }

    /**
     * Form pembelian + riwayat (cari nama barang, filter supplier, paginasi).
     */
    public function index(Request $request)
    {
        $riwayat = PembelianStok::query()
            ->with(['supplier', 'barang'])
            ->when($request->query('q'), fn ($q, $v) => $q->whereHas('barang', fn ($b) => $b->where('nama_barang', 'like', "%{$v}%")))
            ->when($request->query('id_supplier'), fn ($q, $v) => $q->where('id_supplier', $v))
            ->orderByDesc('tanggal_beli')
            ->orderByDesc('id_pembelian')
            ->paginate(8)
            ->withQueryString();

        return view('stok.pembelian', [
            'riwayat' => $riwayat,
            'daftarSupplier' => Supplier::orderBy('nama_supplier')->get(['id_supplier', 'nama_supplier']),
            'daftarBarang' => Barang::orderBy('nama_barang')->orderBy('id_barang')->get(['id_barang', 'nama_barang', 'satuan', 'stok']),
        ]);
    }

    public function store(StorePembelianRequest $request): RedirectResponse
    {
        $pembelian = $this->pembelianService->catat($request->validated());
        $barang = $pembelian->barang;

        return redirect()
            ->route('stok.pembelian.index')
            ->with('status', "Pembelian {$barang->nama_barang} sebanyak {$pembelian->qty_masuk} {$barang->satuan} tersimpan. Stok sekarang: {$barang->stok} {$barang->satuan}.");
    }
}