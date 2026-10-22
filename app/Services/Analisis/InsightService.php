<?php

namespace App\Services\Analisis;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class InsightService
{
    private const HARI = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];

    private const PRIORITAS = ['Tinggi' => 0, 'Sedang' => 1, 'Rendah' => 2];

    public function __construct(private LlmClient $llm)
    {
    }

    public function susun(array $omzet, ?array $ramalan, array $stok): array
    {
        $fakta = $this->fakta($omzet, $ramalan, $stok);
        $aturan = $this->dariAturan($fakta);

        if (! $this->llm->aktif() || ! $fakta['data_cukup']) {
            return $aturan + ['llm_gagal' => false];
        }

        return $this->dariLlm($fakta, $aturan) ?? ($aturan + ['llm_gagal' => true]);
    }

    /* ---------------------------------------------------------------- fakta */

    private function fakta(array $omzet, ?array $ramalan, array $stok): array
    {
        $nilai = array_values($omzet);
        $akhir = Carbon::yesterday();
        $hariAktif = count(array_filter($nilai, fn ($v) => $v > 0));

        $omzet7 = array_sum(array_slice($nilai, -7));
        $omzetSebelum = count($nilai) >= 14 ? array_sum(array_slice($nilai, -14, 7)) : 0;
        $tren = $omzetSebelum > 0 ? round(($omzet7 - $omzetSebelum) / $omzetSebelum * 100, 1) : null;

        $dari28 = $akhir->copy()->subDays(27)->startOfDay();
        $sampai = $akhir->copy()->endOfDay();

        // produk terlaris 28 hari
        $totalBarang28 = (float) DB::table('detail_transaksi AS d')
            ->join('transaksi AS t', 't.id_transaksi', '=', 'd.id_transaksi')
            ->whereBetween('t.waktu_transaksi', [$dari28, $sampai])
            ->sum('d.subtotal');

        $terlaris = DB::table('detail_transaksi AS d')
            ->join('transaksi AS t', 't.id_transaksi', '=', 'd.id_transaksi')
            ->join('barang AS b', 'b.id_barang', '=', 'd.id_barang')
            ->whereBetween('t.waktu_transaksi', [$dari28, $sampai])
            ->groupBy('b.id_barang', 'b.nama_barang')
            ->selectRaw('b.id_barang, b.nama_barang, SUM(d.qty) AS qty, SUM(d.subtotal) AS omzet')
            ->orderByDesc('omzet')
            ->limit(5)
            ->get()
            ->map(fn ($r) => [
                'id_barang' => (int) $r->id_barang,
                'nama' => $r->nama_barang,
                'qty' => (int) $r->qty,
                'omzet' => (float) $r->omzet,
                'porsi_persen' => $totalBarang28 > 0 ? round($r->omzet / $totalBarang28 * 100, 1) : 0,
            ])->all();

        // barang yang penjualannya turun (7 hari terakhir dibanding 7 hari sebelumnya)
        $batas7 = $akhir->copy()->subDays(6)->startOfDay();
        $turun = DB::table('detail_transaksi AS d')
            ->join('transaksi AS t', 't.id_transaksi', '=', 'd.id_transaksi')
            ->join('barang AS b', 'b.id_barang', '=', 'd.id_barang')
            ->whereBetween('t.waktu_transaksi', [$akhir->copy()->subDays(13)->startOfDay(), $sampai])
            ->groupBy('b.id_barang', 'b.nama_barang', 'b.stok')
            ->selectRaw(
                'b.id_barang, b.nama_barang, b.stok,
                 SUM(CASE WHEN t.waktu_transaksi >= ? THEN d.qty ELSE 0 END) AS qty_baru,
                 SUM(CASE WHEN t.waktu_transaksi < ? THEN d.qty ELSE 0 END) AS qty_lama',
                [$batas7, $batas7]
            )
            ->get()
            ->filter(fn ($r) => $r->qty_lama >= 5 && $r->qty_baru <= $r->qty_lama * 0.7)
            ->sortByDesc(fn ($r) => $r->qty_lama - $r->qty_baru)
            ->take(2)
            ->map(fn ($r) => [
                'id_barang' => (int) $r->id_barang,
                'nama' => $r->nama_barang,
                'qty_7_hari_terakhir' => (int) $r->qty_baru,
                'qty_7_hari_sebelumnya' => (int) $r->qty_lama,
                'stok' => (int) $r->stok,
            ])->values()->all();

        // barang yang pernah terjual tetapi sudah >= 21 hari tidak terjual
        $lambat = DB::table('barang AS b')
            ->joinSub(
                DB::table('detail_transaksi AS d')
                    ->join('transaksi AS t', 't.id_transaksi', '=', 'd.id_transaksi')
                    ->groupBy('d.id_barang')
                    ->selectRaw('d.id_barang, MAX(DATE(t.waktu_transaksi)) AS terakhir'),
                'j',
                'j.id_barang',
                '=',
                'b.id_barang'
            )
            ->where('b.stok', '>', 0)
            ->where('j.terakhir', '<=', $akhir->copy()->subDays(20)->toDateString())
            ->orderByDesc('b.stok')
            ->limit(3)
            ->get(['b.id_barang', 'b.nama_barang', 'b.stok', 'j.terakhir'])
            ->map(fn ($r) => [
                'id_barang' => (int) $r->id_barang,
                'nama' => $r->nama_barang,
                'stok' => (int) $r->stok,
                'terakhir_terjual' => $r->terakhir,
            ])->all();

        // busiest day (avg omzet per hari dalam seminggu, 28 hari terakhir)
        $perHari = [];
        foreach (array_slice($omzet, -28, null, true) as $tgl => $v) {
            $dow = Carbon::parse($tgl)->dayOfWeek;
            $perHari[$dow]['jumlah'] = ($perHari[$dow]['jumlah'] ?? 0) + $v;
            $perHari[$dow]['hari'] = ($perHari[$dow]['hari'] ?? 0) + 1;
        }
        $hariRamai = null;
        foreach ($perHari as $dow => $p) {
            $rata = $p['jumlah'] / $p['hari'];
            if ($hariRamai === null || $rata > $hariRamai['rata_omzet']) {
                $hariRamai = ['nama' => self::HARI[$dow], 'rata_omzet' => round($rata)];
            }
        }

        // anomali: omzet harian 7 hari terakhir yang menyimpang jauh dari 28 hari sebelumnya
        $anomali = [];
        $dasar = array_slice($nilai, -35, 28);
        if (count($dasar) >= 14) {
            $mean = array_sum($dasar) / count($dasar);
            $sd = sqrt(array_sum(array_map(fn ($v) => ($v - $mean) ** 2, $dasar)) / count($dasar));

            if ($sd > 0) {
                foreach (array_slice($omzet, -7, null, true) as $tgl => $v) {
                    if (abs($v - $mean) / $sd > 2) {
                        $anomali[] = [
                            'tanggal' => $tgl,
                            'omzet' => round($v),
                            'rata_normal' => round($mean),
                            'arah' => $v > $mean ? 'di atas' : 'di bawah',
                        ];
                    }
                }
            }
        }

        // metode bayar dominan
        $metode = DB::table('transaksi')
            ->whereBetween('waktu_transaksi', [$dari28, $sampai])
            ->selectRaw('metode_bayar, COUNT(*) AS jumlah')
            ->groupBy('metode_bayar')
            ->orderByDesc('jumlah')
            ->get();
        $totalTrx = $metode->sum('jumlah');
        $metodeDominan = $totalTrx > 0
            ? ['metode' => $metode[0]->metode_bayar, 'porsi_persen' => round($metode[0]->jumlah / $totalTrx * 100, 1)]
            : null;

        // ringkasan prakiraan
        $ringkasRamalan = null;
        if ($ramalan) {
            $ke7 = array_sum(array_slice($ramalan['ramalan'], 0, 7));
            $ringkasRamalan = [
                'model' => $ramalan['model'],
                'wape_persen' => $ramalan['wape'] !== null ? round($ramalan['wape'] * 100, 1) : null,
                'omzet_7_hari_ke_depan' => round($ke7),
                'rata_per_hari' => round(array_sum($ramalan['ramalan']) / count($ramalan['ramalan'])),
                'perubahan_vs_7_hari_terakhir_persen' => $omzet7 > 0 ? round(($ke7 - $omzet7) / $omzet7 * 100, 1) : null,
            ];
        }

        $perhatian = collect($stok)
            ->whereIn('status', ['habis', 'kritis', 'waspada'])
            ->take(8)
            ->map(fn ($s) => [
                'id_barang' => $s['id_barang'],
                'nama' => $s['nama'],
                'satuan' => $s['satuan'],
                'stok' => $s['stok'],
                'status' => $s['status'],
                'permintaan_harian' => $s['permintaan_harian'],
                'sisa_hari' => $s['sisa_hari'],
                'estimasi_habis' => $s['estimasi_habis'],
                'rekomendasi_restok' => $s['rekomendasi_restok'],
            ])->values()->all();

        return [
            'periode_berakhir' => $akhir->toDateString(),
            'hari_aktif' => $hariAktif,
            'data_cukup' => $hariAktif >= 14,
            'omzet_7_hari' => round($omzet7),
            'omzet_7_hari_sebelumnya' => round($omzetSebelum),
            'tren_persen' => $tren,
            'terlaris_28_hari' => $terlaris,
            'penjualan_turun' => $turun,
            'barang_lambat' => $lambat,
            'hari_tersibuk' => $hariRamai,
            'anomali' => $anomali,
            'metode_dominan' => $metodeDominan,
            'prakiraan' => $ringkasRamalan,
            'stok_perhatian' => $perhatian,
        ];
    }

    /* --------------------------------------------------------------- aturan */

    private function dariAturan(array $f): array
    {
        if (! $f['data_cukup']) {
            return [
                'sumber' => 'aturan',
                'model' => null,
                'ringkasan' => "Data penjualan belum cukup untuk dianalisis (minimal 14 hari bertransaksi, saat ini {$f['hari_aktif']} hari). Lanjutkan pencatatan transaksi harian lewat kasir.",
                'temuan' => [],
                'next_steps' => [[
                    'prioritas' => 'Sedang',
                    'tindakan' => 'Lanjutkan pencatatan transaksi harian lewat kasir.',
                    'alasan' => 'Prakiraan dan insight membutuhkan riwayat penjualan yang cukup.',
                    'id_barang' => null,
                ]],
            ];
        }

        $rp = fn ($v) => 'Rp ' . number_format($v, 0, ',', '.');
        $persen = fn ($v) => number_format(abs($v), 1, ',', '.') . '%';

        $temuan = [];
        $kalimat = [];

        // tren
        if ($f['tren_persen'] !== null) {
            $arah = $f['tren_persen'] >= 0 ? 'naik' : 'turun';
            $teks = "Omzet 7 hari terakhir {$rp($f['omzet_7_hari'])}, {$arah} {$persen($f['tren_persen'])} dibanding 7 hari sebelumnya ({$rp($f['omzet_7_hari_sebelumnya'])}).";
            $temuan[] = $teks;
            $kalimat[] = $teks;
        }

        // terlaris
        if ($f['terlaris_28_hari']) {
            $top = array_slice($f['terlaris_28_hari'], 0, 3);
            $daftar = implode(', ', array_map(fn ($t) => "{$t['nama']} ({$t['porsi_persen']}% omzet barang)", $top));
            $temuan[] = "Produk terlaris 28 hari terakhir: {$daftar}.";
        }

        if ($f['hari_tersibuk']) {
            $temuan[] = "Hari paling ramai: {$f['hari_tersibuk']['nama']} (rata-rata omzet {$rp($f['hari_tersibuk']['rata_omzet'])}).";
        }

        if ($f['metode_dominan']) {
            $temuan[] = "Metode pembayaran terbanyak: {$f['metode_dominan']['metode']} ({$f['metode_dominan']['porsi_persen']}% transaksi).";
        }

        foreach ($f['anomali'] as $a) {
            $tgl = Carbon::parse($a['tanggal'])->format('d/m/Y');
            $temuan[] = "Omzet {$tgl} ({$rp($a['omzet'])}) {$a['arah']} kisaran normal ({$rp($a['rata_normal'])}).";
        }

        // prakiraan
        if ($f['prakiraan']) {
            $p = $f['prakiraan'];
            $galat = $p['wape_persen'] !== null ? ", galat uji {$p['wape_persen']}%" : '';
            $kalimat[] = "Prakiraan omzet 7 hari ke depan {$rp($p['omzet_7_hari_ke_depan'])} (rata-rata {$rp($p['rata_per_hari'])} per hari; model {$p['model']}{$galat}).";
        }

        $jumlahStok = count($f['stok_perhatian']);
        if ($jumlahStok > 0) {
            $kalimat[] = "Ada {$jumlahStok} barang yang stoknya perlu perhatian.";
        }

        // next step
        $langkah = [];

        foreach (array_slice($f['stok_perhatian'], 0, 5) as $s) {
            $prioritas = $s['status'] === 'waspada' ? 'Sedang' : 'Tinggi';
            $jumlah = $s['rekomendasi_restok'] ? " sekitar {$s['rekomendasi_restok']} {$s['satuan']}" : '';

            if ($s['status'] === 'habis') {
                $alasan = "Stok sudah habis" . ($s['permintaan_harian'] > 0 ? ", dan barang ini diperkirakan terjual {$s['permintaan_harian']} {$s['satuan']} per hari." : '.');
            } else {
                $habis = $s['estimasi_habis'] ? Carbon::parse($s['estimasi_habis'])->format('d/m/Y') : '-';
                $alasan = "Stok {$s['stok']} {$s['satuan']} diperkirakan habis dalam " . number_format($s['sisa_hari'], 1, ',', '.') . " hari (sekitar {$habis}).";
            }

            $langkah[] = [
                'prioritas' => $prioritas,
                'tindakan' => "Restok {$s['nama']}{$jumlah}.",
                'alasan' => $alasan,
                'id_barang' => $s['id_barang'],
            ];
        }

        if ($f['tren_persen'] !== null && $f['tren_persen'] <= -10) {
            $nama = $f['penjualan_turun'] ? ' Mulai dari: ' . implode(', ', array_column($f['penjualan_turun'], 'nama')) . '.' : '';
            $langkah[] = [
                'prioritas' => 'Sedang',
                'tindakan' => "Periksa penyebab turunnya omzet 7 hari terakhir.{$nama}",
                'alasan' => "Omzet turun {$persen($f['tren_persen'])} dibanding minggu sebelumnya.",
                'id_barang' => null,
            ];
        } elseif ($f['tren_persen'] !== null && $f['tren_persen'] >= 10) {
            $langkah[] = [
                'prioritas' => 'Rendah',
                'tindakan' => 'Pastikan stok barang terlaris cukup untuk beberapa hari ke depan.',
                'alasan' => "Omzet naik {$persen($f['tren_persen'])} dibanding minggu sebelumnya, sehingga permintaan berpotensi terus naik.",
                'id_barang' => null,
            ];
        }

        foreach ($f['penjualan_turun'] as $t) {
            if ($t['stok'] <= 0) {
                $langkah[] = [
                    'prioritas' => 'Tinggi',
                    'tindakan' => "Segera isi ulang {$t['nama']}.",
                    'alasan' => "Penjualan turun dari {$t['qty_7_hari_sebelumnya']} ke {$t['qty_7_hari_terakhir']} unit karena stok habis.",
                    'id_barang' => $t['id_barang'],
                ];
            }
        }

        foreach ($f['anomali'] as $a) {
            $langkah[] = [
                'prioritas' => 'Sedang',
                'tindakan' => 'Periksa transaksi pada ' . Carbon::parse($a['tanggal'])->format('d/m/Y') . '.',
                'alasan' => "Omzet {$a['arah']} kisaran normal ({$rp($a['omzet'])} dibanding {$rp($a['rata_normal'])}).",
                'id_barang' => null,
            ];
        }

        if ($f['barang_lambat']) {
            $nama = implode(', ', array_column($f['barang_lambat'], 'nama'));
            $langkah[] = [
                'prioritas' => 'Rendah',
                'tindakan' => "Tinjau barang yang lama tidak laku: {$nama}. Tahan restok atau pertimbangkan promosi.",
                'alasan' => 'Tidak ada penjualan selama 21 hari atau lebih, padahal stok masih ada.',
                'id_barang' => $f['barang_lambat'][0]['id_barang'],
            ];
        }

        usort($langkah, fn ($a, $b) => self::PRIORITAS[$a['prioritas']] <=> self::PRIORITAS[$b['prioritas']]);

        return [
            'sumber' => 'aturan',
            'model' => null,
            'ringkasan' => implode(' ', $kalimat) ?: 'Belum ada ringkasan.',
            'temuan' => $temuan,
            'next_steps' => array_slice($langkah, 0, 8),
        ];
    }

    /* ------------------------------------------------------------------ LLM */

    private function dariLlm(array $fakta, array $aturan): ?array
    {
        $sistem = 'Kamu analis penjualan untuk toko kelontong UMKM di Indonesia. '
            . 'Gunakan HANYA data pada JSON yang diberikan. Dilarang mengarang angka, nama barang, atau tanggal. '
            . 'Tulis dalam Bahasa Indonesia yang ringkas dan mudah dipahami pemilik toko. '
            . 'Balas HANYA dengan satu objek JSON, tanpa teks lain dan tanpa markdown.';

        $pengguna = "Data analisis (JSON):\n"
            . json_encode(['fakta' => $fakta, 'saran_awal' => $aturan['next_steps']], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
            . "\n\nTugas: tulis ringkasan satu paragraf (maksimal 4 kalimat), temuan penting (maksimal 5 butir), "
            . 'dan langkah lanjut (next steps) maksimal 6, diurutkan dari paling mendesak. Format: '
            . '{"ringkasan": string, "temuan": [string], "next_steps": [{"prioritas": "Tinggi|Sedang|Rendah", '
            . '"tindakan": string, "alasan": string, "id_barang": angka atau null}]}. '
            . 'id_barang hanya boleh berasal dari data. Barang berstatus habis atau kritis harus berprioritas Tinggi.';

        $teks = $this->llm->minta($sistem, $pengguna);
        $data = $teks ? $this->ambilJson($teks) : null;

        return $data ? $this->validasi($data, $fakta) : null;
    }

    private function ambilJson(string $teks): ?array
    {
        $awal = strpos($teks, '{');
        $akhir = strrpos($teks, '}');

        if ($awal === false || $akhir === false || $akhir < $awal) {
            return null;
        }

        $data = json_decode(substr($teks, $awal, $akhir - $awal + 1), true);

        return is_array($data) ? $data : null;
    }

    private function validasi(array $data, array $fakta): ?array
    {
        if (! isset($data['ringkasan'], $data['temuan'], $data['next_steps'])
            || ! is_string($data['ringkasan']) || trim($data['ringkasan']) === ''
            || ! is_array($data['temuan']) || ! is_array($data['next_steps'])) {
            return null;
        }

        // id_barang yang boleh dipakai: hanya yang ada pada fakta
        $idSah = [];
        foreach (['terlaris_28_hari', 'penjualan_turun', 'barang_lambat', 'stok_perhatian'] as $kunci) {
            foreach ($fakta[$kunci] as $baris) {
                $idSah[] = $baris['id_barang'];
            }
        }

        $temuan = [];
        foreach ($data['temuan'] as $t) {
            if (! is_string($t)) {
                return null;
            }
            $temuan[] = $t;
        }

        $langkah = [];
        foreach ($data['next_steps'] as $s) {
            if (! is_array($s)
                || ! isset($s['prioritas'], $s['tindakan'], $s['alasan'])
                || ! isset(self::PRIORITAS[$s['prioritas']])
                || ! is_string($s['tindakan']) || ! is_string($s['alasan'])) {
                return null;
            }

            $id = $s['id_barang'] ?? null;
            if ($id !== null && ! in_array((int) $id, $idSah, true)) {
                return null;
            }

            $langkah[] = [
                'prioritas' => $s['prioritas'],
                'tindakan' => $s['tindakan'],
                'alasan' => $s['alasan'],
                'id_barang' => $id !== null ? (int) $id : null,
            ];
        }

        usort($langkah, fn ($a, $b) => self::PRIORITAS[$a['prioritas']] <=> self::PRIORITAS[$b['prioritas']]);

        return [
            'sumber' => 'llm',
            'model' => $this->llm->model(),
            'ringkasan' => $data['ringkasan'],
            'temuan' => array_slice($temuan, 0, 5),
            'next_steps' => array_slice($langkah, 0, 6),
            'llm_gagal' => false,
        ];
    }
}