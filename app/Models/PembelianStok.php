<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PembelianStok extends Model
{
    protected $table = 'pembelian_stok';

    protected $primaryKey = 'id_pembelian';

    public $timestamps = false;

    protected $fillable = [
        'id_supplier',
        'id_barang',
        'tanggal_beli',
        'qty_masuk',
        'harga_kulakan',
    ];

    protected $casts = [
        'tanggal_beli' => 'date:Y-m-d',
        'qty_masuk' => 'integer',
        'harga_kulakan' => 'float',
    ];

    protected $appends = ['total'];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'id_supplier', 'id_supplier');
    }

    public function barang(): BelongsTo
    {
        return $this->belongsTo(Barang::class, 'id_barang', 'id_barang');
    }

    public function getTotalAttribute(): float
    {
        return $this->qty_masuk * $this->harga_kulakan;
    }
}