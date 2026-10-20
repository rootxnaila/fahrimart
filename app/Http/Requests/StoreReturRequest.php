<?php

namespace App\Http\Requests;

use App\Models\Barang;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreReturRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id_barang' => ['required', 'integer', 'exists:barang,id_barang'],
            'jenis' => ['required', Rule::in(['Retur Supplier', 'Barang Rusak'])],
            'qty_rusak' => ['required', 'integer', 'min:1', 'max:1000000'],
            'tanggal_catat' => ['required', 'date', 'before_or_equal:today'],
            'keterangan' => ['nullable', 'string', 'max:100'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $stok = Barang::whereKey($this->input('id_barang'))->value('stok');

            if ($stok !== null && (int) $this->input('qty_rusak') > (int) $stok) {
                $validator->errors()->add('qty_rusak', "Jumlah melebihi stok yang tersedia ({$stok}).");
            }
        });
    }

    public function messages(): array
    {
        return [
            'required' => ':attribute wajib diisi.',
            'integer' => ':attribute harus berupa bilangan bulat.',
            'min' => ':attribute minimal :min.',
            'max' => ':attribute maksimal :max.',
            'keterangan.max' => 'Alasan maksimal :max karakter.',
            'exists' => ':attribute tidak ditemukan.',
            'in' => ':attribute tidak valid.',
            'date' => ':attribute bukan tanggal yang valid.',
            'tanggal_catat.before_or_equal' => 'Tanggal tidak boleh melebihi hari ini.',
        ];
    }

    public function attributes(): array
    {
        return [
            'id_barang' => 'Barang',
            'jenis' => 'Jenis',
            'qty_rusak' => 'Jumlah',
            'tanggal_catat' => 'Tanggal',
            'keterangan' => 'Alasan',
        ];
    }
}