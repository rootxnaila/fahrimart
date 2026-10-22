<?php

namespace App\Console\Commands;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DataDemoCommand extends Command
{
    protected $signature = 'fahrimart:data-demo {--hapus : Hapus semua data demo} {--hari=90 : Jumlah hari riwayat (30-180)}';

    protected $description = 'Buat atau hapus data penjualan SIMULASI (berlabel [DEMO]) untuk menguji fitur analisis AI';

    // faktor ramai per hari dalam seminggu (0 = minggu ... 6 = sabtu)
    private const FAKTOR_HARI = [0 => 1.25, 1 => 0.9, 2 => 0.9, 3 => 0.95, 4 => 1.0, 5 => 1.15, 6 => 1.35];

    private const BARANG = [
        ['[DEMO] Beras Premium 5kg', 'Sembako', 'Pak', 58000, 62000, 6, null, null],
        ['[DEMO] Minyak Goreng 1L', 'Sembako', 'Botol', 14000, 16500, 10, null, null],
        ['[DEMO] Gula Pasir 1kg', 'Sembako', 'Kg', 13000, 15500, 7, 25, 1.04],
        ['[DEMO] Mi Instan', 'Snack', 'Pcs', 2800, 3500, 25, null, null],
        ['[DEMO] Air Mineral 600ml', 'Minuman', 'Botol', 2000, 3000, 18, null, null],
        ['[DEMO] Teh Celup', 'Minuman', 'Dus', 6000, 8000, 4, 20, 1.25],
        ['[DEMO] Kopi Sachet', 'Minuman', 'Pak', 9000, 12000, 3, 30, 0.0],
        ['[DEMO] Sabun Mandi', 'Lainnya', 'Pcs', 3000, 4500, 0.15, null, null],
    ];

    public function handle(): int
    {
        if ($this->option('hapus')) {
            return $this->hapus();
        }

        if (DB::table('barang')->where('nama_barang', 'like', '[DEMO]%')->exists()) {
            $this->error('Data demo sudah ada. Jalankan dengan --hapus lebih dulu.');

            return self::FAILURE;
        }

        $hari = max(30, min(180, (int) $this->option('hari')));
        mt_srand(20261020); // hasil dapat diulang

        $kasir = User::where('role', 'kasir')->value('id_user') ?? User::value('id_user');
        $ppn = (float) config('fahrimart.ppn_persen');
        $mulai = Carbon::today()->subDays($hari);

        $idSupplier = DB::table('supplier')->insertGetId([
            'nama_supplier' => '[DEMO] Supplier Grosir',
            'no_telepon' => '081200000000',
            'alamat' => 'Jl. Simulasi No. 1',
        ]);

        $barang = [];
        foreach (self::BARANG as [$nama, $kategori, $satuan, $beli, $jual, $base, $cutoff, $pengali]) {
            $id = DB::table('barang')->insertGetId([
                'nama_barang' => $nama,
                'kategori' => $kategori,
                'satuan' => $satuan,
                'harga_beli' => $beli,
                'harga_jual' => $jual,
                'stok' => 0,
            ]);

            $barang[$id] = compact('beli', 'jual', 'base', 'cutoff', 'pengali') + ['stok' => 0];
        }

        $nomor = 0;
        $bar = $this->output->createProgressBar($hari);
        $bar->start();

        for ($i = 0; $i < $hari; $i++) {
            $tanggal = $mulai->copy()->addDays($i);
            $sisaHari = $hari - $i;

            DB::transaction(function () use (&$barang, &$nomor, $i, $tanggal, $sisaHari, $hari, $mulai, $idSupplier, $kasir, $ppn) {
                // pembelian stok (trigger yang menambah stok di database)
                foreach ($barang as $id => &$b) {
                    $qtyBeli = $this->qtyPembelian($b, $i, $sisaHari, $hari, $mulai);

                    if ($qtyBeli > 0) {
                        DB::table('pembelian_stok')->insert([
                            'id_supplier' => $idSupplier,
                            'id_barang' => $id,
                            'tanggal_beli' => $tanggal->toDateString(),
                            'qty_masuk' => $qtyBeli,
                            'harga_kulakan' => $b['beli'],
                        ]);
                        $b['stok'] += $qtyBeli;
                    }
                }
                unset($b);

                // permintaan hari ini
                $unit = [];
                $jumlahUnit = 0;

                foreach ($barang as $id => $b) {
                    $lambda = $b['base'] * self::FAKTOR_HARI[$tanggal->dayOfWeek] * (1 + 0.003 * $i);

                    if ($b['base'] < 1 && $sisaHari <= 24) {
                        $lambda = 0.0; // barang lambat: berhenti laku di 24 hari terakhir
                    }

                    $deterministik = $b['cutoff'] !== null && $sisaHari <= $b['cutoff'];
                    $qty = min($b['stok'], $deterministik ? (int) round($lambda) : $this->poisson($lambda));

                    if ($qty > 0) {
                        $unit[$id] = $qty;
                        $jumlahUnit += $qty;
                    }
                }

                if ($jumlahUnit === 0) {
                    return;
                }

                // bagi menjadi beberapa transaksi
                $jumlahTrx = max(1, (int) round($jumlahUnit / 3.2));
                $keranjang = array_fill(0, $jumlahTrx, []);

                foreach ($unit as $id => $qty) {
                    for ($u = 0; $u < $qty; $u++) {
                        $k = mt_rand(0, $jumlahTrx - 1);
                        $keranjang[$k][$id] = ($keranjang[$k][$id] ?? 0) + 1;
                    }
                }

                // simpan transaksi (trigger yang mengurangi stok di database)
                foreach ($keranjang as $isi) {
                    if (! $isi) {
                        continue;
                    }

                    $subtotal = 0;
                    foreach ($isi as $id => $qty) {
                        $subtotal += $qty * $barang[$id]['jual'];
                    }

                    $totalBelanja = $subtotal + round($subtotal * $ppn / 100);
                    $metode = $this->pilihMetode();
                    $idTrx = 'DMO-' . sprintf('%05d', ++$nomor);

                    DB::table('transaksi')->insert([
                        'id_transaksi' => $idTrx,
                        'id_pelanggan' => null,
                        'waktu_transaksi' => $tanggal->copy()->setTime(mt_rand(8, 20), mt_rand(0, 59), mt_rand(0, 59)),
                        'nama_pembeli_walkin' => 'Demo',
                        'total_belanja' => $totalBelanja,
                        'jumlah_bayar' => $metode === 'Tunai' ? ceil($totalBelanja / 1000) * 1000 : $totalBelanja,
                        'metode_bayar' => $metode,
                        'id_user' => $kasir,
                    ]);

                    $detail = [];
                    foreach ($isi as $id => $qty) {
                        $detail[] = [
                            'id_transaksi' => $idTrx,
                            'id_barang' => $id,
                            'qty' => $qty,
                            'harga_satuan' => $barang[$id]['jual'],
                            'subtotal' => $qty * $barang[$id]['jual'],
                        ];
                        $barang[$id]['stok'] -= $qty;
                    }

                    DB::table('detail_transaksi')->insert($detail);
                }
            });

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        Cache::forget('fahrimart.analisis.v1');

        $this->info("Selesai: {$nomor} transaksi simulasi selama {$hari} hari.");
        $this->table(
            ['Barang demo', 'Stok akhir'],
            DB::table('barang')->where('nama_barang', 'like', '[DEMO]%')->orderBy('id_barang')->get(['nama_barang', 'stok'])
                ->map(fn ($r) => [$r->nama_barang, $r->stok])->all()
        );
        $this->warn('Ini data SIMULASI. Hapus dengan: php artisan fahrimart:data-demo --hapus');

        return self::SUCCESS;
    }

    private function qtyPembelian(array $b, int $i, int $sisaHari, int $hari, Carbon $mulai): int
    {
        // stok awal
        if ($i === 0) {
            return $b['base'] >= 1 ? (int) ceil($b['base'] * 20) : 30;
        }

        // restok berkala: bila stok di bawah kebutuhan 7 hari
        if ($b['cutoff'] === null || $sisaHari > $b['cutoff']) {
            return ($b['base'] >= 1 && $b['stok'] < $b['base'] * 7) ? (int) ceil($b['base'] * 14) : 0;
        }

        // pembelian terakhir: menutup (pengali x kebutuhan sisa periode), supaya stok akhir menipis
        if ($sisaHari === $b['cutoff']) {
            $kebutuhan = 0.0;
            for ($j = $i; $j < $hari; $j++) {
                $kebutuhan += $b['base'] * self::FAKTOR_HARI[$mulai->copy()->addDays($j)->dayOfWeek] * (1 + 0.003 * $j);
            }

            return max(0, (int) ceil($b['pengali'] * $kebutuhan - $b['stok']));
        }

        return 0;
    }

    private function poisson(float $lambda): int
    {
        if ($lambda <= 0) {
            return 0;
        }

        $batas = exp(-$lambda);
        $k = 0;
        $p = 1.0;

        do {
            $k++;
            $p *= mt_rand() / mt_getrandmax();
        } while ($p > $batas);

        return $k - 1;
    }

    private function pilihMetode(): string
    {
        $r = mt_rand(1, 100);

        return $r <= 55 ? 'Tunai' : ($r <= 85 ? 'QRIS' : 'Debit');
    }

    private function hapus(): int
    {
        $idBarang = DB::table('barang')->where('nama_barang', 'like', '[DEMO]%')->pluck('id_barang')->all();
        $idSupplier = DB::table('supplier')->where('nama_supplier', 'like', '[DEMO]%')->pluck('id_supplier')->all();

        try {
            DB::transaction(function () use ($idBarang, $idSupplier) {
                DB::table('transaksi')->where('id_transaksi', 'like', 'DMO-%')->delete(); // detail ikut terhapus (CASCADE)

                if ($idBarang) {
                    DB::table('retur_rusak')->whereIn('id_barang', $idBarang)->delete();
                    DB::table('pembelian_stok')->whereIn('id_barang', $idBarang)->delete();
                }
                if ($idSupplier) {
                    DB::table('pembelian_stok')->whereIn('id_supplier', $idSupplier)->delete();
                }
                if ($idBarang) {
                    DB::table('barang')->whereIn('id_barang', $idBarang)->delete();
                }
                if ($idSupplier) {
                    DB::table('supplier')->whereIn('id_supplier', $idSupplier)->delete();
                }
            });
        } catch (QueryException $e) {
            $this->error('Gagal menghapus: ada transaksi nyata yang memakai barang demo. Hapus transaksi itu lebih dulu.');

            return self::FAILURE;
        }

        Cache::forget('fahrimart.analisis.v1');
        $this->info('Data demo dihapus.');

        return self::SUCCESS;
    }
}