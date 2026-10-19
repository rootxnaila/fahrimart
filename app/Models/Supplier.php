<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    protected $table = 'supplier';

    protected $primaryKey = 'id_supplier';

    public $timestamps = false;

    public function getKodeSupplierAttribute(): string
    {
        return sprintf('SUP%03d', $this->id_supplier);
    }
}