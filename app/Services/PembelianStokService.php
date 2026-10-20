<?php

namespace App\Services;

use App\Models\PembelianStok;

class PembelianStokService
{
    public function catat(array $data): PembelianStok
    {
        $pembelian = PembelianStok::create($data);

        return $pembelian->load(['supplier', 'barang']);
    }
}