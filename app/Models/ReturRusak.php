<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReturRusak extends Model
{
    protected $table = 'retur_rusak';

    protected $primaryKey = 'id_rusak';

    public $timestamps = false;

    protected $fillable = [
        'id_barang',
        'jenis',
        'tanggal_catat',
        'qty_rusak',
        'keterangan',
    ];

    protected $casts = [
        'tanggal_catat' => 'date:Y-m-d',
        'qty_rusak' => 'integer',
    ];

    public function barang(): BelongsTo
    {
        return $this->belongsTo(Barang::class, 'id_barang', 'id_barang');
    }

    public function getJenisSingkatAttribute(): string
    {
        return $this->jenis === 'Retur Supplier' ? 'Retur' : 'Rusak';
    }
}