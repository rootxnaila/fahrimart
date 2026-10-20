<?php

namespace App\Services;

use App\Models\Barang;
use App\Models\Transaksi;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TransaksiService
{
    private const BATAS_TOTAL = 99999999.99;
    public function proses(User $kasir, array $data): array
    {
        // gabung baris barang yang sama
        $qtyPerBarang = [];
        foreach ($data['items'] as $item) {
            $id = (int) $item['id_barang'];
            $qtyPerBarang[$id] = ($qtyPerBarang[$id] ?? 0) + (int) $item['qty'];
        }

        // ambil harga dan stok dari database, lalu periksa kecukupan stok
        $barang = Barang::whereIn('id_barang', array_keys($qtyPerBarang))->get()->keyBy('id_barang');

        $baris = [];
        $subtotal = 0.0;
        $kurang = [];

        foreach ($qtyPerBarang as $idBarang => $qty) {
            $b = $barang[$idBarang];

            if ($qty > $b->stok) {
                $kurang[] = "{$b->nama_barang} (diminta {$qty}, tersedia {$b->stok})";
                continue;
            }

            $baris[] = ['id_barang' => $idBarang, 'qty' => $qty, 'harga_satuan' => $b->harga_jual];
            $subtotal += $b->harga_jual * $qty;
        }

        if ($kurang) {
            throw ValidationException::withMessages([
                'items' => 'Stok tidak mencukupi: ' . implode('; ', $kurang) . '.',
            ]);
        }

        // itung PPN dan total (PPN tidak disimpan, bisa diturunkan dari total - subtotal)
        $subtotal = round($subtotal, 2);
        $ppn = round($subtotal * config('fahrimart.ppn_persen') / 100, 0);
        $total = round($subtotal + $ppn, 2);

        if ($total > self::BATAS_TOTAL) {
            throw ValidationException::withMessages(['items' => 'Total belanja melebihi batas yang dapat dicatat.']);
        }

        // paymeny Tunai harus cukup, QRIS dan Debit dianggap pas
        if ($data['metode_bayar'] === 'Tunai') {
            $jumlahBayar = (float) $data['jumlah_bayar'];

            if ($jumlahBayar < $total) {
                throw ValidationException::withMessages([
                    'jumlah_bayar' => 'Jumlah bayar kurang dari total belanja (' . number_format($total, 0, ',', '.') . ').',
                ]);
            }
        } else {
            $jumlahBayar = $total;
        }

        // save secara atomik (ulangi bila ID transaksi kebetulan kembar)
        for ($percobaan = 1; ; $percobaan++) {
            try {
                $idTransaksi = DB::transaction(
                    fn () => $this->simpan($kasir, $data, $baris, $total, $jumlahBayar)
                );

                break;
            } catch (QueryException $e) {
                if ($this->stokTidakCukup($e)) {
                    throw ValidationException::withMessages([
                        'items' => 'Stok tidak mencukupi. Transaksi dibatalkan, tidak ada data yang tersimpan.',
                    ]);
                }

                if ($percobaan < 3 && $this->idKembar($e)) {
                    continue;
                }

                throw $e;
            }
        }

        return $this->struk($idTransaksi);
    }

    public function struk(string $idTransaksi): array
    {
        $baris = DB::select('CALL get_struk_transaksi(?)', [$idTransaksi]);

        if (! $baris) {
            throw (new ModelNotFoundException())->setModel(Transaksi::class, [$idTransaksi]);
        }

        $kepala = $baris[0];
        $subtotal = round(collect($baris)->sum(fn ($r) => (float) $r->subtotal), 2);
        $total = (float) $kepala->total_belanja;

        return [
            'id_transaksi' => $kepala->id_transaksi,
            'waktu_transaksi' => $kepala->waktu_transaksi,
            'pembeli' => $kepala->pembeli,
            'metode_bayar' => $kepala->metode_bayar,
            'items' => collect($baris)->map(fn ($r) => [
                'nama_barang' => $r->nama_barang,
                'qty' => (int) $r->qty,
                'harga_satuan' => (float) $r->harga_satuan,
                'subtotal' => (float) $r->subtotal,
            ])->all(),
            'ringkasan' => [
                'subtotal' => $subtotal,
                'ppn' => round($total - $subtotal, 2),
                'total' => $total,
                'jumlah_bayar' => (float) $kepala->jumlah_bayar,
                'kembalian' => (float) $kepala->kembalian,
            ],
        ];
    }

    private function simpan(User $kasir, array $data, array $baris, float $total, float $jumlahBayar): string
    {
        $idTransaksi = $this->idBerikutnya();

        DB::statement('CALL add_transaksi(?, ?, ?, ?, ?, ?, ?)', [
            $idTransaksi,
            $data['id_pelanggan'] ?? null,
            $data['nama_pembeli'] ?? null,
            $total,
            $data['metode_bayar'],
            $jumlahBayar,
            $kasir->id_user,
        ]);

        foreach ($baris as $b) {
            DB::statement('CALL add_detail_transaksi(?, ?, ?, ?)', [
                $idTransaksi,
                $b['id_barang'],
                $b['qty'],
                $b['harga_satuan'],
            ]);
        }

        return $idTransaksi;
    }

    private function idBerikutnya(): string
    {
        $terakhir = DB::table('transaksi')
            ->where('id_transaksi', 'like', 'TRX-%')
            ->orderByRaw('CAST(SUBSTRING(id_transaksi, 5) AS UNSIGNED) DESC')
            ->lockForUpdate()
            ->value('id_transaksi');

        $nomor = $terakhir ? ((int) substr($terakhir, 4)) + 1 : 1;

        return sprintf('TRX-%03d', $nomor);
    }

    private function stokTidakCukup(QueryException $e): bool
    {
        return $e->getCode() === '23000' && str_contains($e->getMessage(), 'chk_barang_stok');
    }

    private function idKembar(QueryException $e): bool
    {
        return $e->getCode() === '23000' && str_contains($e->getMessage(), 'Duplicate entry');
    }
}