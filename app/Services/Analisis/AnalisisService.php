<?php

namespace App\Services\Analisis;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class AnalisisService
{
    private const KUNCI = 'fahrimart.analisis.v1';

    public function __construct(
        private PenjualanData $data,
        private Forecaster $forecaster,
        private StockAlertService $stok,
        private InsightService $insight,
    ) {
    }


    public function ambil(bool $segarkan = false): array
    {
        if ($segarkan) {
            Cache::forget(self::KUNCI);
        }

        return Cache::remember(
            self::KUNCI,
            now()->addMinutes((int) config('fahrimart.analisis.cache_menit')),
            fn () => $this->hitung()
        );
    }

    private function hitung(): array
    {
        $c = config('fahrimart.analisis');
        $horizon = $c['horizon_hari'];

        $sampai = Carbon::yesterday();
        $dari = $sampai->copy()->subDays($c['riwayat_hari'] - 1);

        $omzet = $this->data->omzetHarian($dari, $sampai);
        $seri = $this->forecaster->potongNolAwal(array_values($omzet));
        $ramalan = $this->forecaster->ramalkan($seri, $horizon);

        $tanggalRamalan = [];
        for ($i = 1; $i <= $horizon; $i++) {
            $tanggalRamalan[] = $sampai->copy()->addDays($i)->toDateString();
        }

        $stok = $this->stok->hitung();

        return [
            'dibuat_pada' => now()->toDateTimeString(),
            'penjualan' => [
                'riwayat' => ['tanggal' => array_keys($omzet), 'omzet' => array_values($omzet)],
                'ramalan' => $ramalan ? $ramalan + ['tanggal' => $tanggalRamalan] : null,
            ],
            'stok' => $stok,
            'insight' => $this->insight->susun($omzet, $ramalan, $stok),
        ];
    }
}