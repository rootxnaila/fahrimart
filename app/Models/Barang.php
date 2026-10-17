<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Barang extends Model
{
    protected $table = 'barang';

    protected $primaryKey = 'id_barang';

    public $timestamps = false;

    protected $casts = [
        'harga_beli' => 'float',
        'harga_jual' => 'float',
        'stok' => 'integer',
    ];


    public function getKodeBarangAttribute(): string
    {
        return sprintf('BRG%03d', $this->id_barang);
    }

    public function getStatusStokAttribute(): string
    {
        if ($this->stok <= 0) {
            return 'habis';
        }

        if ($this->stok <= config('fahrimart.batas_stok_menipis')) {
            return 'hampir_habis';
        }

        return 'aman';
    }

    public function getLabelStatusAttribute(): string
    {
        return match ($this->status_stok) {
            'habis' => 'Habis',
            'hampir_habis' => 'Hampir Habis',
            default => 'Stok Aman',
        };
    }
}