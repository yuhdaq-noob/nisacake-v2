<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

// Request untuk validasi pembuatan order/pesanan baru
class StoreOrderRequest extends FormRequest
{
    // Mengizinkan semua user melakukan request ini
    public function authorize(): bool
    {
        return true;
    }

    // Aturan validasi input pesanan
    public function rules(): array
    {
        return [
            'customer_name' => 'required|string|max:255',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|integer|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
        ];
    }

    // Pesan error kustom untuk validasi
    public function messages(): array
    {
        return [
            'customer_name.required' => 'Nama pelanggan wajib diisi.',
            'customer_name.string' => 'Nama pelanggan harus berupa teks.',
            'customer_name.max' => 'Nama pelanggan maksimal 255 karakter.',
            'items.required' => 'Minimal ada 1 item dalam pesanan.',
            'items.array' => 'Items harus berupa array.',
            'items.min' => 'Pesanan tidak boleh kosong.',
            'items.*.product_id.required' => 'ID produk wajib diisi untuk setiap item.',
            'items.*.product_id.exists' => 'Produk yang dipilih tidak ditemukan.',
            'items.*.quantity.required' => 'Kuantitas produk wajib diisi.',
            'items.*.quantity.integer' => 'Kuantitas harus berupa angka.',
            'items.*.quantity.min' => 'Kuantitas minimal 1.',
        ];
    }
}
