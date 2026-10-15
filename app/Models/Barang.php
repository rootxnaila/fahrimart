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
}