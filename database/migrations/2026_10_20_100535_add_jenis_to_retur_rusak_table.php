<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::table('retur_rusak', function (Blueprint $table) {
            $table->enum('jenis', ['Retur Supplier', 'Barang Rusak'])
                ->default('Barang Rusak')
                ->after('id_barang');
        });
    }

    public function down(): void
    {
        Schema::table('retur_rusak', function (Blueprint $table) {
            $table->dropColumn('jenis');
        });
    }
};