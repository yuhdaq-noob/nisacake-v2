<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

// Request untuk validasi update harga bahan baku
class UpdateMaterialPriceRequest extends FormRequest
{
    // Mengizinkan semua user melakukan request ini
    public function authorize(): bool
    {
        return true;
    }

    // Aturan validasi input harga bahan baku
    public function rules(): array
    {
        return [
            'price_per_base_unit' => 'required|numeric|min:0.01',
        ];
    }

    // Pesan error kustom untuk validasi
    public function messages(): array
    {
        return [
            'price_per_base_unit.required' => 'Harga per kg wajib diisi.',
            'price_per_base_unit.numeric' => 'Harga harus berupa angka (contoh: 35000 untuk Rp 35.000).',
            'price_per_base_unit.min' => 'Harga minimal 0.01.',
        ];
    }
}
