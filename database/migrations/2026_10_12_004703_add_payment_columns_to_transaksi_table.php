<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transaksi', function (Blueprint $table) {
            $table->decimal('jumlah_bayar', 10, 2)->default(0)->after('total_belanja');
            $table->unsignedBigInteger('id_user')->nullable()->after('metode_bayar');
            $table->foreign('id_user')->references('id_user')->on('users')->nullOnDelete();
        });

        DB::statement('UPDATE transaksi SET jumlah_bayar = total_belanja');
    }

    public function down(): void
    {
        Schema::table('transaksi', function (Blueprint $table) {
            $table->dropForeign(['id_user']);
            $table->dropColumn(['id_user', 'jumlah_bayar']);
        });
    }
};