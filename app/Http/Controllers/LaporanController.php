<?php

namespace App\Http\Controllers;

use App\Services\Analisis\AnalisisService;
use App\Services\Analisis\PenjualanData;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LaporanController extends Controller
{
    private const BATAS_BARIS_PDF = 300;

    public function __construct(private PenjualanData $data, private AnalisisService $analisis)
    {
    }

    public function index(Request $request)
    {
        [$dari, $sampai] = $this->rentang($request);
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

    public function pdf(Request $request)
    {
        [$dari, $sampai] = $this->rentang($request);
        $rentang = [$dari->copy()->startOfDay(), $sampai->copy()->endOfDay()];

        $totalPendapatan = (float) DB::table('transaksi')->whereBetween('waktu_transaksi', $rentang)->sum('total_belanja');
        $totalTransaksi = DB::table('transaksi')->whereBetween('waktu_transaksi', $rentang)->count();

        $perMetode = DB::table('transaksi')
            ->whereBetween('waktu_transaksi', $rentang)
            ->selectRaw('metode_bayar, COUNT(*) AS jumlah, SUM(total_belanja) AS total')
            ->groupBy('metode_bayar')
            ->orderByDesc('total')
            ->get();

        $terlaris = DB::table('detail_transaksi AS d')
            ->join('transaksi AS t', 't.id_transaksi', '=', 'd.id_transaksi')
            ->join('barang AS b', 'b.id_barang', '=', 'd.id_barang')
            ->whereBetween('t.waktu_transaksi', $rentang)
            ->groupBy('b.id_barang', 'b.nama_barang', 'b.satuan')
            ->selectRaw('b.nama_barang, b.satuan, SUM(d.qty) AS qty, SUM(d.subtotal) AS omzet')
            ->orderByDesc('qty')
            ->limit(10)
            ->get();

        $riwayat = DB::table('transaksi AS t')
            ->leftJoin('users AS u', 'u.id_user', '=', 't.id_user')
            ->whereBetween('t.waktu_transaksi', $rentang)
            ->orderByDesc('t.waktu_transaksi')
            ->limit(self::BATAS_BARIS_PDF)
            ->select('t.id_transaksi', 't.waktu_transaksi', 't.total_belanja', 't.metode_bayar', 'u.nama AS kasir')
            ->get();

        $analisis = $this->analisis->ambil();
        $ramalan = $analisis['penjualan']['ramalan'];

        $barisRamalan = [];
        if ($ramalan) {
            foreach (array_slice($ramalan['tanggal'], 0, 7) as $i => $tanggal) {
                $barisRamalan[] = [
                    'tanggal' => $tanggal,
                    'ramalan' => $ramalan['ramalan'][$i],
                    'bawah' => $ramalan['bawah'][$i],
                    'atas' => $ramalan['atas'][$i],
                ];
            }
        }

        $pdf = Pdf::loadView('laporan.pdf', [
            'toko' => [
                'nama' => config('fahrimart.toko_nama'),
                'alamat' => config('fahrimart.toko_alamat'),
            ],
            'dari' => $dari,
            'sampai' => $sampai,
            'pengguna' => $request->user(),
            'dicetakPada' => now(),
            'totalPendapatan' => $totalPendapatan,
            'totalTransaksi' => $totalTransaksi,
            'perMetode' => $perMetode,
            'terlaris' => $terlaris,
            'ramalan' => $ramalan,
            'barisRamalan' => $barisRamalan,
            'perhatian' => collect($analisis['stok'])->whereIn('status', ['habis', 'kritis', 'waspada'])->values(),
            'insight' => $analisis['insight'],
            'dibuatPada' => Carbon::parse($analisis['dibuat_pada']),
            'riwayat' => $riwayat,
            'batasBaris' => self::BATAS_BARIS_PDF,
        ])->setPaper('a4', 'portrait');

        return $pdf->download(sprintf('laporan-penjualan-%s_sd_%s.pdf', $dari->toDateString(), $sampai->toDateString()));
    }

    public function perbarui(): RedirectResponse
    {
        $this->analisis->ambil(true);

        return back()->with('status', 'Analisis diperbarui.');
    }


    private function rentang(Request $request): array
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

        return [$dari, $sampai];
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