<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

// Resource untuk format data bahan baku pada produk
class MaterialForProductResource extends JsonResource
{
    // Mengubah data model menjadi array untuk response API
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'unit' => $this->unit,
            'base_unit' => $this->base_unit,
            'price_per_unit' => $this->price_per_unit,
            'price_per_base_unit' => $this->price_per_base_unit,
            'quantity_needed' => $this->pivot->quantity_needed,
            'total_cost' => $this->price_per_unit * $this->pivot->quantity_needed,
        ];
    }
}
