<?php

namespace App\Http\Requests;

class StoreBarangRequest extends BarangRequest
{
    public function rules(): array
    {
        return parent::rules() + [
            'stok' => ['required', 'integer', 'min:0', 'max:2147483647'],
        ];
    }
}