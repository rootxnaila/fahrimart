<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePembelianRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id_supplier' => ['required', 'integer', 'exists:supplier,id_supplier'],
            'id_barang' => ['required', 'integer', 'exists:barang,id_barang'],
            'qty_masuk' => ['required', 'integer', 'min:1', 'max:1000000'],
            'harga_kulakan' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'tanggal_beli' => ['required', 'date', 'before_or_equal:today'],
        ];
    }

    public function messages(): array
    {
        return [
            'required' => ':attribute wajib diisi.',
            'integer' => ':attribute harus berupa bilangan bulat.',
            'numeric' => ':attribute harus berupa angka.',
            'min' => ':attribute minimal :min.',
            'max' => ':attribute maksimal :max.',
            'exists' => ':attribute tidak ditemukan.',
            'date' => ':attribute bukan tanggal yang valid.',
            'tanggal_beli.before_or_equal' => 'Tanggal pembelian tidak boleh melebihi hari ini.',
        ];
    }

    public function attributes(): array
    {
        return [
            'id_supplier' => 'Supplier',
            'id_barang' => 'Barang',
            'qty_masuk' => 'Stok masuk',
            'harga_kulakan' => 'Harga beli',
            'tanggal_beli' => 'Tanggal',
        ];
    }
}