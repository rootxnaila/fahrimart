<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTransaksiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id_pelanggan' => ['nullable', 'integer', 'exists:pelanggan,id_pelanggan'],
            'nama_pembeli' => ['nullable', 'string', 'max:100'],
            'metode_bayar' => ['required', Rule::in(['Tunai', 'QRIS', 'Debit'])],
            'jumlah_bayar' => ['required_if:metode_bayar,Tunai', 'nullable', 'numeric', 'min:0', 'max:99999999.99'],
            'items' => ['required', 'array', 'min:1', 'max:100'],
            'items.*.id_barang' => ['required', 'integer', 'exists:barang,id_barang'],
            'items.*.qty' => ['required', 'integer', 'min:1', 'max:100000'],
        ];
    }

    public function messages(): array
    {
        return [
            'required' => ':attribute wajib diisi.',
            'required_if' => 'Jumlah bayar wajib diisi untuk pembayaran Tunai.',
            'integer' => ':attribute harus berupa bilangan bulat.',
            'numeric' => ':attribute harus berupa angka.',
            'min' => ':attribute minimal :min.',
            'max' => ':attribute maksimal :max.',
            'exists' => ':attribute tidak ditemukan.',
            'in' => ':attribute tidak valid.',
            'items.required' => 'Keranjang tidak boleh kosong.',
            'items.min' => 'Keranjang tidak boleh kosong.',
            'items.max' => 'Keranjang maksimal :max baris barang.',
        ];
    }

    public function attributes(): array
    {
        return [
            'id_pelanggan' => 'Pelanggan',
            'nama_pembeli' => 'Nama pembeli',
            'metode_bayar' => 'Metode pembayaran',
            'jumlah_bayar' => 'Jumlah bayar',
            'items.*.id_barang' => 'Barang',
            'items.*.qty' => 'Jumlah',
        ];
    }
}