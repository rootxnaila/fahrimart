<?php

namespace App\Services\Analisis;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PenjualanData
{
    //omzet harian (total_belanja, termasuk PPN). hari tanpa transaksi bernilai 0.
    
    public function omzetHarian(Carbon $dari, Carbon $sampai): array
    {
        $baris = DB::table('transaksi')
            ->selectRaw('DATE(waktu_transaksi) AS tgl, SUM(total_belanja) AS omzet')
            ->whereBetween('waktu_transaksi', [$dari->copy()->startOfDay(), $sampai->copy()->endOfDay()])
            ->groupBy('tgl')
            ->pluck('omzet', 'tgl')
            ->all();

        return $this->isiHari($dari, $sampai, $baris);
    }

    // jumlah terjual per barang per hari (hanya barang yang pernah terjual pada rentang ini).
    
    public function qtyHarianPerBarang(Carbon $dari, Carbon $sampai): array
    {
        $baris = DB::table('detail_transaksi AS d')
            ->join('transaksi AS t', 't.id_transaksi', '=', 'd.id_transaksi')
            ->selectRaw('d.id_barang, DATE(t.waktu_transaksi) AS tgl, SUM(d.qty) AS qty')
            ->whereBetween('t.waktu_transaksi', [$dari->copy()->startOfDay(), $sampai->copy()->endOfDay()])
            ->groupBy('d.id_barang', 'tgl')
            ->get();

        $perBarang = [];
        foreach ($baris as $r) {
            $perBarang[$r->id_barang][$r->tgl] = (float) $r->qty;
        }

        foreach ($perBarang as $id => $data) {
            $perBarang[$id] = $this->isiHari($dari, $sampai, $data);
        }

        return $perBarang;
    }

    private function isiHari(Carbon $dari, Carbon $sampai, array $data): array
    {
        $hasil = [];

        for ($t = $dari->copy()->startOfDay(); $t->lte($sampai); $t->addDay()) {
            $kunci = $t->toDateString();
            $hasil[$kunci] = (float) ($data[$kunci] ?? 0);
        }

        return $hasil;
    }
}