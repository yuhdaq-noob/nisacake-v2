<?php

// FIXME: PERHITUNGAN

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

// Resource untuk format data produk
class ProductResource extends JsonResource
{
    // Mengubah data model menjadi array untuk response API
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'selling_price' => $this->selling_price,
            // FIXME: TIDAK DIPAKAI
            // production_cost & profit_per_unit saat ini tidak dipakai oleh halaman kasir/gudang/laporan.
            // Perhitungan laporan pakai orders.total_hpp vs orders.total_price.
            'production_cost' => $this->production_cost,
            // LOGIKA: PERHITUNGAN Laba Kotor
            'profit_per_unit' => $this->selling_price - $this->production_cost,
            'materials' => MaterialForProductResource::collection($this->whenLoaded('materials')),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
        ];
    }
}
