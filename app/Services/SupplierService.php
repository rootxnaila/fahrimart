<?php

namespace App\Services;

use App\Models\Supplier;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class SupplierService
{
    public function tambah(array $data): Supplier
    {
        DB::statement('CALL add_supplier(?, ?, ?)', [
            $data['nama_supplier'],
            $data['no_telepon'] ?? null,
            $data['alamat'] ?? null,
        ]);

        $id = DB::selectOne('SELECT LAST_INSERT_ID() AS id')->id;

        return Supplier::findOrFail($id);
    }

    public function ubah(Supplier $supplier, array $data): Supplier
    {
        DB::statement('CALL update_supplier(?, ?, ?, ?)', [
            $supplier->id_supplier,
            $data['nama_supplier'],
            $data['no_telepon'] ?? null,
            $data['alamat'] ?? null,
        ]);

        return $supplier->fresh();
    }

    public function hapus(Supplier $supplier): bool
    {
        try {
            DB::statement('CALL delete_supplier(?)', [$supplier->id_supplier]);
        } catch (QueryException $e) {
            if ($e->getCode() === '23000') {
                return false;
            }

            throw $e;
        }

        return true;
    }
}