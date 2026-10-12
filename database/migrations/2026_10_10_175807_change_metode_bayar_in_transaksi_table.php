<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    //payment method 
    public function up(): void
    {
        if (DB::table('transaksi')->where('metode_bayar', 'Kasbon')->exists()) {
            throw new RuntimeException('Masih ada transaksi Kasbon. Selesaikan dulu sebelum migrate.');
        }

        DB::statement("ALTER TABLE transaksi MODIFY metode_bayar ENUM('Tunai','QRIS','Debit') NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE transaksi MODIFY metode_bayar ENUM('Tunai','QRIS','Kasbon') NOT NULL");
    }
};