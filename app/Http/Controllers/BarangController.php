<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBarangRequest;
use App\Http\Requests\UpdateBarangRequest;
use App\Models\Barang;
use App\Services\BarangService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BarangController extends Controller
{
    public function __construct(private BarangService $barangService)
    {
    }

    public function index(Request $request)
    {
        $batas = config('fahrimart.batas_stok_menipis');

        $barang = Barang::query()
            ->when($request->query('q'), fn ($q, $v) => $q->where('nama_barang', 'like', "%{$v}%"))
            ->when($request->query('kategori'), fn ($q, $v) => $q->where('kategori', $v))
            ->when($request->query('status'), function ($q, $status) use ($batas) {
                return match ($status) {
                    'habis' => $q->where('stok', '<=', 0),
                    'hampir_habis' => $q->where('stok', '>', 0)->where('stok', '<=', $batas),
                    'aman' => $q->where('stok', '>', $batas),
                    default => $q,
                };
            })
            ->orderBy('nama_barang')
            ->orderBy('id_barang')
            ->paginate(8)
            ->withQueryString();

        $kategori = Barang::query()
            ->select('kategori')
            ->distinct()
            ->orderBy('kategori')
            ->pluck('kategori');

        $kategoriSaran = $kategori
            ->merge(['Beras', 'Minyak', 'Snack', 'Makanan', 'Minuman', 'Lainnya'])
            ->unique()
            ->sort()
            ->values();

        return view('barang.index', compact('barang', 'kategori', 'kategoriSaran'));
    }

    public function store(StoreBarangRequest $request): RedirectResponse
    {
        $barang = $this->barangService->tambah($request->validated());

        return redirect()
            ->route('master.barang.index')
            ->with('status', "Barang {$barang->nama_barang} berhasil ditambahkan.");
    }

    public function update(UpdateBarangRequest $request, Barang $barang): RedirectResponse
    {
        $barang = $this->barangService->ubah($barang, $request->validated());

        return redirect()
            ->back()
            ->with('status', "Barang {$barang->nama_barang} berhasil diubah.");
    }

    public function destroy(Barang $barang): RedirectResponse
    {
        $nama = $barang->nama_barang;

        if (! $this->barangService->hapus($barang)) {
            return redirect()
                ->back()
                ->with('error', "Barang {$nama} tidak dapat dihapus karena sudah dipakai pada transaksi, pembelian stok, atau retur.");
        }

        return redirect()
            ->back()
            ->with('status', "Barang {$nama} berhasil dihapus.");
    }
}