<?php

namespace App\Services;

use App\Models\ReturRusak;
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;

class ReturRusakService
{
    /**
     * Catat retur atau barang rusak.
     *
     * Pengurangan stok dilakukan oleh trigger kurangi_stok_rusak di database,
     * BUKAN oleh kode ini. Jika stok akan menjadi negatif (mis. dua pengguna
     * mencatat bersamaan), CHECK constraint menolak dan kita ubah menjadi
     * pesan validasi yang ramah.
     */
    public function catat(array $data): ReturRusak
    {
        try {
            $retur = ReturRusak::create($data);
        } catch (QueryException $e) {
            if ($e->getCode() === '23000' && str_contains($e->getMessage(), 'chk_barang_stok')) {
                throw ValidationException::withMessages([
                    'qty_rusak' => 'Stok tidak mencukupi untuk jumlah tersebut.',
                ]);
            }

            throw $e;
        }

        return $retur->load('barang');
    }
}