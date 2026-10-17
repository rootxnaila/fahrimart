<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use Illuminate\Http\Request;

class BarangController extends Controller
{
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

        return view('barang.index', compact('barang', 'kategori'));
    }
}