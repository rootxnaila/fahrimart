<?php

namespace App\Services\Analisis;

use App\Models\Barang;
use Carbon\Carbon;

class StockAlertService
{
    public const LABEL = [
        'habis' => 'Habis',
        'kritis' => 'Kritis',
        'waspada' => 'Waspada',
        'aman' => 'Aman',
        'tanpa_data' => 'Data belum cukup',
    ];

    public function __construct(private PenjualanData $data, private Forecaster $forecaster)
    {
    }

    // status stok semua barang, diurutkan dari yang paling mendesak.
    // only read data: stok tidak pernah diubah di sini.
     
    public function hitung(): array
    {
        $c = config('fahrimart.analisis');
        $sampai = Carbon::yesterday();
        $dari = $sampai->copy()->subDays($c['riwayat_hari'] - 1);
        $qty = $this->data->qtyHarianPerBarang($dari, $sampai);

        $hasil = [];
        foreach (Barang::orderBy('nama_barang')->orderBy('id_barang')->get() as $b) {
            $hasil[] = $this->nilai($b, $qty[$b->id_barang] ?? [], $c);
        }

        usort($hasil, fn ($a, $b) => [$this->urutan($a['status']), $a['sisa_hari'] ?? PHP_INT_MAX]
            <=> [$this->urutan($b['status']), $b['sisa_hari'] ?? PHP_INT_MAX]);

        return $hasil;
    }

    private function nilai(Barang $b, array $harian, array $c): array
    {
        $horizon = $c['horizon_hari'];
        $stok = (int) $b->stok;

        $seri = $this->forecaster->potongNolAwal(array_values($harian));
        $fc = $this->forecaster->ramalkan($seri, $horizon);
        $d = $fc ? array_sum($fc['ramalan']) / $horizon : 0.0; // kebutuhan harian

        $sisa = null;
        $status = 'aman';

        if ($stok <= 0) {
            $status = 'habis';
        } elseif ($fc === null) {
            $status = $stok <= config('fahrimart.batas_stok_menipis') ? 'waspada' : 'tanpa_data';
        } elseif ($d > 0) {
            $sisa = $stok / $d;

            if ($sisa <= $c['lead_time_hari']) {
                $status = 'kritis';
            } elseif ($sisa <= $c['lead_time_hari'] + $c['buffer_hari']) {
                $status = 'waspada';
            }
        }

        $rekomendasi = ($fc && $d > 0)
            ? max(0, (int) ceil($d * ($c['lead_time_hari'] + $c['target_cover_hari'] + $c['stok_pengaman_hari']) - $stok))
            : null;

        $estimasi = null;
        if ($status === 'habis') {
            $estimasi = Carbon::today()->toDateString();
        } elseif ($sisa !== null) {
            $estimasi = Carbon::today()->addDays((int) floor($sisa))->toDateString();
        }

        return [
            'id_barang' => $b->id_barang,
            'kode' => $b->kode_barang,
            'nama' => $b->nama_barang,
            'satuan' => $b->satuan,
            'stok' => $stok,
            'permintaan_harian' => round($d, 2),
            'sisa_hari' => $sisa !== null ? round($sisa, 1) : null,
            'estimasi_habis' => $estimasi,
            'rekomendasi_restok' => $rekomendasi,
            'status' => $status,
            'label' => self::LABEL[$status],
            'model' => $fc['model'] ?? null,
            'wape' => $fc['wape'] ?? null,
        ];
    }

    private function urutan(string $status): int
    {
        return ['habis' => 0, 'kritis' => 1, 'waspada' => 2, 'tanpa_data' => 3, 'aman' => 4][$status];
    }
}