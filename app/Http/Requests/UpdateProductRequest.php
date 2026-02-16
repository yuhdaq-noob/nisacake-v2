<?php

// FIXME: PERHITUNGAN

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

// Request untuk validasi update data produk
class UpdateProductRequest extends FormRequest
{
    // Mengizinkan semua user melakukan request ini
    public function authorize(): bool
    {
        return true;
    }

    // Aturan validasi input update produk
    public function rules(): array
    {
        return [
            'name' => 'sometimes|string|max:255|unique:products,name,' . $this->product->id,
            'selling_price' => 'sometimes|numeric|min:0|max:99999999.99',
            // FIXME: TIDAK DIPAKAI
            // production_cost belum dipakai untuk HPP otomatis/laporan.
            'production_cost' => 'sometimes|numeric|min:0|max:99999999.99',
            'overhead_cost_per_unit' => 'sometimes|numeric|min:0|max:99999999.99',
            'description' => 'sometimes|string|max:1000|nullable',
        ];
    }

    // Pesan error kustom untuk validasi
    public function messages(): array
    {
        return [
            'name.unique' => 'Nama produk sudah ada',
            'name.string' => 'Nama produk harus berupa teks',
            'selling_price.numeric' => 'Harga jual harus berupa angka',
            'selling_price.min' => 'Harga jual tidak boleh negatif',
            'production_cost.numeric' => 'Biaya produksi harus berupa angka',
            'production_cost.min' => 'Biaya produksi tidak boleh negatif',
            'overhead_cost_per_unit.numeric' => 'Biaya overhead harus berupa angka',
            'overhead_cost_per_unit.min' => 'Biaya overhead tidak boleh negatif',
        ];
    }
}
