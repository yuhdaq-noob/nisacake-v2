<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

// Request untuk validasi penambahan stok bahan baku
class StoreStockRequest extends FormRequest
{
    // Mengizinkan semua user melakukan request ini
    public function authorize(): bool
    {
        return true;
    }

    // Aturan validasi input stok
    public function rules(): array
    {
        return [
            'material_id' => 'required|integer|exists:materials,id',
            'amount' => 'required|integer|min:1',
            'description' => 'required|string|max:255',
        ];
    }

    // Pesan error kustom untuk validasi
    public function messages(): array
    {
        return [
            'material_id.required' => 'ID bahan baku wajib diisi.',
            'material_id.exists' => 'Bahan baku yang dipilih tidak ditemukan.',
            'amount.required' => 'Jumlah stok wajib diisi.',
            'amount.integer' => 'Jumlah stok harus berupa angka.',
            'amount.min' => 'Jumlah stok minimal 1.',
            'description.required' => 'Deskripsi wajib diisi.',
            'description.string' => 'Deskripsi harus berupa teks.',
            'description.max' => 'Deskripsi maksimal 255 karakter.',
        ];
    }
}
