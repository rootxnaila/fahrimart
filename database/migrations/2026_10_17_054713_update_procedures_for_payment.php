<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // header transaksi: tambah jumlah_bayar dan id_user (kasir)
        DB::unprepared('DROP PROCEDURE IF EXISTS add_transaksi');
        DB::unprepared("
            CREATE PROCEDURE add_transaksi (
                IN p_id_transaksi VARCHAR(20),
                IN p_id_pelanggan INT,
                IN p_nama_pembeli VARCHAR(100),
                IN p_total DECIMAL(10,2),
                IN p_metode ENUM('Tunai','QRIS','Debit'),
                IN p_jumlah_bayar DECIMAL(10,2),
                IN p_id_user BIGINT UNSIGNED
            )
            BEGIN
                INSERT INTO transaksi (id_transaksi, id_pelanggan, nama_pembeli_walkin, total_belanja, metode_bayar, jumlah_bayar, id_user)
                VALUES (p_id_transaksi, p_id_pelanggan, p_nama_pembeli, p_total, p_metode, p_jumlah_bayar, p_id_user);
            END
        ");

        // prosedur 1 barang: hanya enum yang disesuaikan (tidak dipakai POS multi-item)
        DB::unprepared('DROP PROCEDURE IF EXISTS proses_transaksi_kasir');
        DB::unprepared("
            CREATE PROCEDURE proses_transaksi_kasir (
                IN p_id_transaksi VARCHAR(20),
                IN p_id_pelanggan INT,
                IN p_nama_pembeli VARCHAR(100),
                IN p_total DECIMAL(10,2),
                IN p_metode ENUM('Tunai','QRIS','Debit'),
                IN p_id_barang INT,
                IN p_qty INT,
                IN p_harga_satuan DECIMAL(10,2)
            )
            BEGIN
                DECLARE EXIT HANDLER FOR SQLEXCEPTION
                BEGIN
                    ROLLBACK;
                    SELECT 'Transaksi Gagal! Terjadi kesalahan pada sistem.' AS Pesan;
                END;

                START TRANSACTION;

                INSERT INTO transaksi (id_transaksi, id_pelanggan, nama_pembeli_walkin, total_belanja, metode_bayar)
                VALUES (p_id_transaksi, p_id_pelanggan, p_nama_pembeli, p_total, p_metode);

                INSERT INTO detail_transaksi (id_transaksi, id_barang, qty, harga_satuan, subtotal)
                VALUES (p_id_transaksi, p_id_barang, p_qty, p_harga_satuan, (p_qty * p_harga_satuan));

                COMMIT;
                SELECT 'Transaksi Berhasil Disimpan!' AS Pesan;
            END
        ");

        // struk: tambah jumlah_bayar dan kembalian (dihitung, tidak disimpan)
        DB::unprepared('DROP PROCEDURE IF EXISTS get_struk_transaksi');
        DB::unprepared("
            CREATE PROCEDURE get_struk_transaksi (IN p_id_transaksi VARCHAR(20))
            BEGIN
                SELECT
                    t.id_transaksi,
                    t.waktu_transaksi,
                    IFNULL(p.nama_pelanggan, t.nama_pembeli_walkin) AS pembeli,
                    b.nama_barang,
                    dt.qty,
                    dt.harga_satuan,
                    dt.subtotal,
                    t.total_belanja,
                    t.metode_bayar,
                    t.jumlah_bayar,
                    (t.jumlah_bayar - t.total_belanja) AS kembalian
                FROM transaksi t
                LEFT JOIN pelanggan p ON t.id_pelanggan = p.id_pelanggan
                JOIN detail_transaksi dt ON t.id_transaksi = dt.id_transaksi
                JOIN barang b ON dt.id_barang = b.id_barang
                WHERE t.id_transaksi = p_id_transaksi;
            END
        ");
    }

    /**
     * Mengembalikan definisi lama (sesuai dump awal).
     */
    public function down(): void
    {
        DB::unprepared('DROP PROCEDURE IF EXISTS add_transaksi');
        DB::unprepared("
            CREATE PROCEDURE add_transaksi (
                IN p_id_transaksi VARCHAR(20),
                IN p_id_pelanggan INT,
                IN p_nama_pembeli VARCHAR(100),
                IN p_total DECIMAL(10,2),
                IN p_metode ENUM('Tunai','QRIS','Kasbon')
            )
            BEGIN
                INSERT INTO transaksi (id_transaksi, id_pelanggan, nama_pembeli_walkin, total_belanja, metode_bayar)
                VALUES (p_id_transaksi, p_id_pelanggan, p_nama_pembeli, p_total, p_metode);
            END
        ");

        DB::unprepared('DROP PROCEDURE IF EXISTS proses_transaksi_kasir');
        DB::unprepared("
            CREATE PROCEDURE proses_transaksi_kasir (
                IN p_id_transaksi VARCHAR(20),
                IN p_id_pelanggan INT,
                IN p_nama_pembeli VARCHAR(100),
                IN p_total DECIMAL(10,2),
                IN p_metode ENUM('Tunai','QRIS','Kasbon'),
                IN p_id_barang INT,
                IN p_qty INT,
                IN p_harga_satuan DECIMAL(10,2)
            )
            BEGIN
                DECLARE EXIT HANDLER FOR SQLEXCEPTION
                BEGIN
                    ROLLBACK;
                    SELECT 'Transaksi Gagal! Terjadi kesalahan pada sistem.' AS Pesan;
                END;

                START TRANSACTION;

                INSERT INTO transaksi (id_transaksi, id_pelanggan, nama_pembeli_walkin, total_belanja, metode_bayar)
                VALUES (p_id_transaksi, p_id_pelanggan, p_nama_pembeli, p_total, p_metode);

                INSERT INTO detail_transaksi (id_transaksi, id_barang, qty, harga_satuan, subtotal)
                VALUES (p_id_transaksi, p_id_barang, p_qty, p_harga_satuan, (p_qty * p_harga_satuan));

                COMMIT;
                SELECT 'Transaksi Berhasil Disimpan!' AS Pesan;
            END
        ");

        DB::unprepared('DROP PROCEDURE IF EXISTS get_struk_transaksi');
        DB::unprepared("
            CREATE PROCEDURE get_struk_transaksi (IN p_id_transaksi VARCHAR(20))
            BEGIN
                SELECT
                    t.id_transaksi,
                    t.waktu_transaksi,
                    IFNULL(p.nama_pelanggan, t.nama_pembeli_walkin) AS pembeli,
                    b.nama_barang,
                    dt.qty,
                    dt.harga_satuan,
                    dt.subtotal,
                    t.total_belanja,
                    t.metode_bayar
                FROM transaksi t
                LEFT JOIN pelanggan p ON t.id_pelanggan = p.id_pelanggan
                JOIN detail_transaksi dt ON t.id_transaksi = dt.id_transaksi
                JOIN barang b ON dt.id_barang = b.id_barang
                WHERE t.id_transaksi = p_id_transaksi;
            END
        ");
    }
};