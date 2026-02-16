<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

// Request untuk validasi pengurangan stok bahan baku
class ReduceStockRequest extends FormRequest
{
    // Mengizinkan semua user melakukan request ini
    public function authorize(): bool
    {
        return true;
    }

    // Aturan validasi input
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
            'amount.required' => 'Jumlah pengurangan stok wajib diisi.',
            'amount.integer' => 'Jumlah harus berupa angka.',
            'amount.min' => 'Jumlah minimal 1.',
            'description.required' => 'Keterangan pengurangan stok wajib diisi.',
            'description.string' => 'Keterangan harus berupa teks.',
            'description.max' => 'Keterangan maksimal 255 karakter.',
        ];
    }
}
