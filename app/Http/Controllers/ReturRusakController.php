<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReturRequest;
use App\Models\Barang;
use App\Models\ReturRusak;
use App\Services\ReturRusakService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ReturRusakController extends Controller
{
    public function __construct(private ReturRusakService $returService)
    {
    }

    public function index(Request $request)
    {
        $riwayat = ReturRusak::query()
            ->with('barang')
            ->when($request->query('q'), fn ($q, $v) => $q->whereHas('barang', fn ($b) => $b->where('nama_barang', 'like', "%{$v}%")))
            ->when($request->query('jenis'), fn ($q, $v) => $q->where('jenis', $v))
            ->orderByDesc('tanggal_catat')
            ->orderByDesc('id_rusak')
            ->paginate(8)
            ->withQueryString();

        return view('stok.retur', [
            'riwayat' => $riwayat,
            'daftarBarang' => Barang::orderBy('nama_barang')->orderBy('id_barang')->get(['id_barang', 'nama_barang', 'satuan', 'stok']),
        ]);
    }

    public function store(StoreReturRequest $request): RedirectResponse
    {
        $retur = $this->returService->catat($request->validated());
        $barang = $retur->barang;

        return redirect()
            ->route('stok.retur.index')
            ->with('status', "{$retur->jenis}: {$barang->nama_barang} sebanyak {$retur->qty_rusak} {$barang->satuan} tercatat. Stok sekarang: {$barang->stok} {$barang->satuan}.");
    }
}