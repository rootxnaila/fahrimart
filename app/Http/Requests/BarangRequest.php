<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BarangRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nama_barang' => ['required', 'string', 'max:100'],
            'kategori' => ['required', 'string', 'max:50'],
            'satuan' => ['required', 'string', 'max:20'],
            'harga_beli' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'harga_jual' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'foto' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'], 
        ];
    }

    public function messages(): array
    {
        return [
            'required' => ':attribute wajib diisi.',
            'string' => ':attribute harus berupa teks.',
            'numeric' => ':attribute harus berupa angka.',
            'integer' => ':attribute harus berupa bilangan bulat.',
            'min' => ':attribute tidak boleh kurang dari :min.',
            'max' => ':attribute melebihi batas (maksimal :max).',
            'image' => ':attribute harus berupa file gambar.',
            'mimes' => 'Format :attribute yang diizinkan hanya: jpeg, png, jpg, atau webp.',
            'foto.max' => 'Ukuran :attribute maksimal adalah 2MB (2048 KB).',
        ];
    }

    public function attributes(): array
    {
        return [
            'nama_barang' => 'Nama barang',
            'kategori' => 'Kategori',
            'satuan' => 'Satuan',
            'harga_beli' => 'Harga beli',
            'harga_jual' => 'Harga jual',
            'stok' => 'Stok awal',
            'foto' => 'Foto produk', 
        ];
    }
}