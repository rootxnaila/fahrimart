<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SupplierRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nama_supplier' => ['required', 'string', 'max:100'],
            'no_telepon' => ['nullable', 'string', 'max:15', 'regex:/^[0-9+\-\s]+$/'],
            'alamat' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'required' => ':attribute wajib diisi.',
            'string' => ':attribute harus berupa teks.',
            'max' => ':attribute maksimal :max karakter.',
            'no_telepon.regex' => 'Nomor telepon hanya boleh berisi angka, spasi, tanda + atau tanda -.',
        ];
    }

    public function attributes(): array
    {
        return [
            'nama_supplier' => 'Nama supplier',
            'no_telepon' => 'Nomor telepon',
            'alamat' => 'Alamat',
        ];
    }
}