<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

// Resource untuk format data produk
class ProductResource extends JsonResource
{
    // Ubah model menjadi array untuk response API
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'selling_price' => $this->selling_price,
            // Catatan: field ini belum dipakai oleh UI saat ini
            'production_cost' => $this->production_cost,
            // Hitung laba kotor per unit
            'profit_per_unit' => $this->selling_price - $this->production_cost,
            'materials' => MaterialForProductResource::collection($this->whenLoaded('materials')),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
        ];
    }
}
