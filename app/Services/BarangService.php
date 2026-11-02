<?php

namespace App\Services;

use App\Models\Barang;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class BarangService
{
    public function tambah(array $data): Barang
    {
        DB::statement('CALL add_barang(?, ?, ?, ?, ?, ?)', [
            $data['nama_barang'],
            $data['kategori'],
            $data['satuan'],
            $data['harga_beli'],
            $data['harga_jual'],
            $data['stok'],
        ]);

        $id = DB::selectOne('SELECT LAST_INSERT_ID() AS id')->id;
        $barang = Barang::findOrFail($id);

        if (isset($data['foto'])) {
            $barang->foto = $data['foto'];
            $barang->save();
        }

        return $barang;
    }

    public function ubah(Barang $barang, array $data): Barang
    {
        DB::statement('CALL update_barang(?, ?, ?, ?, ?, ?)', [
            $barang->id_barang,
            $data['nama_barang'],
            $data['kategori'],
            $data['satuan'],
            $data['harga_beli'],
            $data['harga_jual'],
        ]);

        if (array_key_exists('foto', $data)) {
            $barang->foto = $data['foto'];
            $barang->save();
        }

        return $barang->fresh();
    }

    public function hapus(Barang $barang): bool
    {
        try {
            DB::statement('CALL delete_barang(?)', [$barang->id_barang]);
        } catch (QueryException $e) {
            if ($e->getCode() === '23000') {
                return false;
            }

            throw $e;
        }

        return true;
    }
}