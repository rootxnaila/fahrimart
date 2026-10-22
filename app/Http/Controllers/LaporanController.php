<?php

namespace App\Http\Controllers;

use App\Services\Analisis\AnalisisService;
use App\Services\Analisis\PenjualanData;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LaporanController extends Controller
{
    public function __construct(private PenjualanData $data, private AnalisisService $analisis)
    {
    }

    public function index(Request $request)
    {
        $input = $request->validate([
            'dari' => ['nullable', 'date'],
            'sampai' => ['nullable', 'date'],
        ], ['date' => 'Tanggal tidak valid.']);

        $sampai = isset($input['sampai']) ? Carbon::parse($input['sampai'])->startOfDay() : Carbon::today();
        $dari = isset($input['dari']) ? Carbon::parse($input['dari'])->startOfDay() : $sampai->copy()->subDays(29);

        if ($dari->gt($sampai)) {
            [$dari, $sampai] = [$sampai, $dari];
        }
        if ($dari->diffInDays($sampai) > 365) {
            $dari = $sampai->copy()->subDays(365);
        }

        $rentang = [$dari->copy()->startOfDay(), $sampai->copy()->endOfDay()];

        $totalPendapatan = (float) DB::table('transaksi')->whereBetween('waktu_transaksi', $rentang)->sum('total_belanja');
        $totalTransaksi = DB::table('transaksi')->whereBetween('waktu_transaksi', $rentang)->count();

        $tren = $this->data->omzetHarian($dari, $sampai);
        $chartTren = [
            'label' => array_map(fn ($t) => Carbon::parse($t)->format('d/m'), array_keys($tren)),
            'nilai' => array_values($tren),
        ];

        $riwayat = DB::table('transaksi AS t')
            ->leftJoin('users AS u', 'u.id_user', '=', 't.id_user')
            ->whereBetween('t.waktu_transaksi', $rentang)
            ->orderByDesc('t.waktu_transaksi')
            ->select('t.id_transaksi', 't.waktu_transaksi', 't.total_belanja', 't.metode_bayar', 'u.nama AS kasir')
            ->paginate(10)
            ->withQueryString();

        $analisis = $this->analisis->ambil();

        return view('laporan.index', [
            'dari' => $dari,
            'sampai' => $sampai,
            'totalPendapatan' => $totalPendapatan,
            'totalTransaksi' => $totalTransaksi,
            'chartTren' => $chartTren,
            'chartRamalan' => $this->chartRamalan($analisis),
            'ramalan' => $analisis['penjualan']['ramalan'],
            'perhatian' => collect($analisis['stok'])->whereIn('status', ['habis', 'kritis', 'waspada'])->values(),
            'insight' => $analisis['insight'],
            'dibuatPada' => Carbon::parse($analisis['dibuat_pada']),
            'riwayat' => $riwayat,
        ]);
    }

    public function perbarui(): RedirectResponse
    {
        $this->analisis->ambil(true);

        return back()->with('status', 'Analisis diperbarui.');
    }

    private function chartRamalan(array $analisis): ?array
    {
        $ramalan = $analisis['penjualan']['ramalan'];

        if (! $ramalan) {
            return null;
        }

        $riwayat = $analisis['penjualan']['riwayat'];
        $tanggal = array_slice($riwayat['tanggal'], -14);
        $nilai = array_slice($riwayat['omzet'], -14);
        $nHistori = count($tanggal);
        $nRamalan = count($ramalan['tanggal']);

        return [
            'label' => array_map(fn ($t) => Carbon::parse($t)->format('d/m'), array_merge($tanggal, $ramalan['tanggal'])),
            'aktual' => array_merge($nilai, array_fill(0, $nRamalan, null)),
            'prediksi' => array_merge(array_fill(0, $nHistori - 1, null), [end($nilai)], $ramalan['ramalan']),
            'bawah' => array_merge(array_fill(0, $nHistori, null), $ramalan['bawah']),
            'atas' => array_merge(array_fill(0, $nHistori, null), $ramalan['atas']),
        ];
    }
}