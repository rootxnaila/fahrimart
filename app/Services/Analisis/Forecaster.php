<?php

namespace App\Services\Analisis;


class Forecaster
{
    private const MUSIM = 7;

    public function ramalkan(array $y, int $horizon): ?array
    {
        $y = array_values(array_map('floatval', $y));
        $n = count($y);

        if ($n < 7) {
            return null;
        }

        $rasioNol = count(array_filter($y, fn ($v) => $v <= 0)) / $n;

        // permintaan putus putus: pakai avg 28 hari
        if ($n >= 14 && $rasioNol > 0.5) {
            return $this->hasil('rata-rata 28 hari', null, array_fill(0, $horizon, $this->rata($y, 28)), 0.0);
        }

        // data masih pendek: pakai avg 7 hari
        if ($n < 14) {
            return $this->hasil('rata-rata 7 hari', null, array_fill(0, $horizon, $this->rata($y, 7)), 0.0);
        }

        // backtest: latih pada data kecuali last 7 day, uji pada 7 hari itu
        $latih = array_slice($y, 0, $n - 7);
        $uji = array_slice($y, -7);

        $kandidat = [];
        foreach ($this->model() as $nama => [$fn, $minLatih]) {
            if (count($latih) < $minLatih) {
                continue;
            }

            $prediksi = $fn($latih, 7);
            $kandidat[] = [
                'nama' => $nama,
                'fn' => $fn,
                'wape' => $this->wape($uji, $prediksi),
                'prediksi' => $prediksi,
            ];
        }

        usort($kandidat, fn ($a, $b) => $a['wape'] <=> $b['wape']);
        $terbaik = $kandidat[0];

        // Simpangan galat pada data uji, dipakai untuk rentang kepercayaan
        $jumlahKuadrat = 0.0;
        foreach ($uji as $i => $aktual) {
            $jumlahKuadrat += ($aktual - $terbaik['prediksi'][$i]) ** 2;
        }
        $sigma = sqrt($jumlahKuadrat / 7);

        return $this->hasil($terbaik['nama'], $terbaik['wape'], ($terbaik['fn'])($y, $horizon), $sigma);
    }

    // buang hari hari kosong di awal seri (sebelum penjualan pertama).
    public function potongNolAwal(array $seri): array
    {
        foreach ($seri as $i => $v) {
            if ($v > 0) {
                return array_slice($seri, $i);
            }
        }

        return [];
    }

    private function model(): array
    {
        return [
            'naive musiman' => [fn ($y, $h) => $this->naiveMusiman($y, $h), 14],
            'rata-rata 7 hari' => [fn ($y, $h) => array_fill(0, $h, $this->rata($y, 7)), 7],
            'holt (tren teredam)' => [fn ($y, $h) => $this->holt($y, $h), 10],
            'holt-winters (musiman mingguan)' => [fn ($y, $h) => $this->holtWinters($y, $h), 21],
        ];
    }

    private function naiveMusiman(array $y, int $h): array
    {
        $n = count($y);
        $m = self::MUSIM;
        $hasil = [];

        for ($i = 0; $i < $h; $i++) {
            $hasil[] = $y[$n - $m + ($i % $m)];
        }

        return $hasil;
    }

    private function rata(array $y, int $k): float
    {
        $bagian = array_slice($y, -min($k, count($y)));

        return array_sum($bagian) / count($bagian);
    }

    // holt dengan tren teredam. Parameter alfa dan beta dicari lewat grid (minimal SSE satu langkah ke depan).

    private function holt(array $y, int $h, float $phi = 0.9): array
    {
        $n = count($y);
        $t0 = ($y[min(6, $n - 1)] - $y[0]) / max(1, min(6, $n - 1));
        $terbaik = null;

        foreach ([0.1, 0.3, 0.5, 0.7, 0.9] as $a) {
            foreach ([0.05, 0.1, 0.2, 0.4] as $b) {
                $l = $y[0];
                $t = $t0;
                $sse = 0.0;

                for ($i = 1; $i < $n; $i++) {
                    $sse += ($y[$i] - ($l + $phi * $t)) ** 2;
                    $lBaru = $a * $y[$i] + (1 - $a) * ($l + $phi * $t);
                    $t = $b * ($lBaru - $l) + (1 - $b) * $phi * $t;
                    $l = $lBaru;
                }

                if ($terbaik === null || $sse < $terbaik[0]) {
                    $terbaik = [$sse, $l, $t];
                }
            }
        }

        [, $l, $t] = $terbaik;
        $hasil = [];
        $kumulatif = 0.0;

        for ($i = 1; $i <= $h; $i++) {
            $kumulatif += $phi ** $i;
            $hasil[] = max(0.0, $l + $kumulatif * $t);
        }

        return $hasil;
    }

    // holt winters aditif dengan musim mingguan. Parameter alfa, beta, gamma dicari lewat grid.
    private function holtWinters(array $y, int $h): array
    {
        $m = self::MUSIM;
        $n = count($y);

        $l0 = array_sum(array_slice($y, 0, $m)) / $m;
        $t0 = (array_sum(array_slice($y, $m, $m)) / $m - $l0) / $m;
        $s0 = [];
        for ($i = 0; $i < $m; $i++) {
            $s0[$i] = $y[$i] - $l0;
        }

        $terbaik = null;

        foreach ([0.1, 0.3, 0.5, 0.7] as $a) {
            foreach ([0.01, 0.05, 0.1] as $b) {
                foreach ([0.1, 0.3, 0.5] as $g) {
                    $l = $l0;
                    $t = $t0;
                    $s = $s0;
                    $sse = 0.0;

                    for ($i = $m; $i < $n; $i++) {
                        $k = $i % $m;
                        $sse += ($y[$i] - ($l + $t + $s[$k])) ** 2;
                        $lBaru = $a * ($y[$i] - $s[$k]) + (1 - $a) * ($l + $t);
                        $t = $b * ($lBaru - $l) + (1 - $b) * $t;
                        $s[$k] = $g * ($y[$i] - $lBaru) + (1 - $g) * $s[$k];
                        $l = $lBaru;
                    }

                    if ($terbaik === null || $sse < $terbaik[0]) {
                        $terbaik = [$sse, $l, $t, $s];
                    }
                }
            }
        }

        [, $l, $t, $s] = $terbaik;
        $hasil = [];

        for ($i = 0; $i < $h; $i++) {
            $hasil[] = max(0.0, $l + ($i + 1) * $t + $s[($n + $i) % $m]);
        }

        return $hasil;
    }

    // WAPE = jumlah |aktual - prediksi| / jumlah aktual (stabil untuk data banyak nol).
    private function wape(array $aktual, array $prediksi): float
    {
        $jumlah = array_sum(array_map('abs', $aktual));

        if ($jumlah <= 0) {
            return INF;
        }

        $galat = 0.0;
        foreach ($aktual as $i => $a) {
            $galat += abs($a - $prediksi[$i]);
        }

        return $galat / $jumlah;
    }

    private function hasil(string $model, ?float $wape, array $ramalan, float $sigma): array
    {
        $z = 1.28; // sekitar 80%
        $ramalan = array_map(fn ($v) => round(max(0.0, $v), 2), $ramalan);

        return [
            'model' => $model,
            'wape' => ($wape !== null && is_finite($wape)) ? round($wape, 4) : null,
            'ramalan' => $ramalan,
            'bawah' => array_map(fn ($v) => round(max(0.0, $v - $z * $sigma), 2), $ramalan),
            'atas' => array_map(fn ($v) => round($v + $z * $sigma, 2), $ramalan),
        ];
    }
}