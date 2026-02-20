<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

// Request untuk validasi pembuatan produk baru
class StoreProductRequest extends FormRequest
{
    // Mengizinkan semua user melakukan request ini
    public function authorize(): bool
    {
        return true;
    }

    // Aturan validasi input produk
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255|unique:products,name',
            'selling_price' => 'required|numeric|min:0|max:99999999.99',
            // Catatan: production_cost belum dipakai untuk HPP otomatis/laporan
            'production_cost' => 'nullable|numeric|min:0|max:99999999.99',
            'overhead_cost_per_unit' => 'nullable|numeric|min:0|max:99999999.99',
            'description' => 'nullable|string|max:1000',
        ];
    }

    // Pesan error kustom untuk validasi
    public function messages(): array
    {
        return [
            'name.required' => 'Nama produk harus diisi',
            'name.unique' => 'Nama produk sudah ada',
            'selling_price.required' => 'Harga jual harus diisi',
            'selling_price.numeric' => 'Harga jual harus berupa angka',
            'selling_price.min' => 'Harga jual tidak boleh negatif',
            'production_cost.numeric' => 'Biaya produksi harus berupa angka',
            'production_cost.min' => 'Biaya produksi tidak boleh negatif',
            'overhead_cost_per_unit.numeric' => 'Biaya overhead harus berupa angka',
            'overhead_cost_per_unit.min' => 'Biaya overhead tidak boleh negatif',
        ];
    }
}
