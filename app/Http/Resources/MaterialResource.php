<?php

// FIXME: PERHITUNGAN

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

// Resource untuk format data bahan baku
class MaterialResource extends JsonResource
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
            'current_stock' => $this->current_stock,
            'min_stock_level' => $this->min_stock_level,
            // FIXME: PERHITUNGAN
            'status' => $this->current_stock <= $this->min_stock_level ? 'Low Stock' : 'OK',
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
        ];
    }
}
