<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * rules stock integrity:
     * - stok barang tidak boleh negatif
     * - qty penjualan, pembelian, dan barang rusak harus lebih dari nol
     */
    public function up(): void
    {
        $this->pastikanDataBersih();

        DB::statement('ALTER TABLE barang ADD CONSTRAINT chk_barang_stok CHECK (stok >= 0)');
        DB::statement('ALTER TABLE detail_transaksi ADD CONSTRAINT chk_detail_qty CHECK (qty > 0)');
        DB::statement('ALTER TABLE pembelian_stok ADD CONSTRAINT chk_beli_qty CHECK (qty_masuk > 0)');
        DB::statement('ALTER TABLE retur_rusak ADD CONSTRAINT chk_rusak_qty CHECK (qty_rusak > 0)');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE retur_rusak DROP CONSTRAINT chk_rusak_qty');
        DB::statement('ALTER TABLE pembelian_stok DROP CONSTRAINT chk_beli_qty');
        DB::statement('ALTER TABLE detail_transaksi DROP CONSTRAINT chk_detail_qty');
        DB::statement('ALTER TABLE barang DROP CONSTRAINT chk_barang_stok');
    }

    private function pastikanDataBersih(): void
    {
        $pelanggaran = [
            'barang.stok < 0' => DB::table('barang')->where('stok', '<', 0)->count(),
            'detail_transaksi.qty <= 0' => DB::table('detail_transaksi')->where('qty', '<=', 0)->count(),
            'pembelian_stok.qty_masuk <= 0' => DB::table('pembelian_stok')->where('qty_masuk', '<=', 0)->count(),
            'retur_rusak.qty_rusak <= 0' => DB::table('retur_rusak')->where('qty_rusak', '<=', 0)->count(),
        ];

        foreach ($pelanggaran as $aturan => $jumlah) {
            if ($jumlah > 0) {
                throw new RuntimeException("Ada {$jumlah} baris yang melanggar {$aturan}. Bersihkan dulu sebelum migrate.");
            }
        }
    }
};