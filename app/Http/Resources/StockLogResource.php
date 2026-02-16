<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

// Resource untuk format data log stok
class StockLogResource extends JsonResource
{
    // Mengubah data model menjadi array untuk response API
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'material_id' => $this->material_id,
            'material' => [
                'id' => $this->material?->id,
                'name' => $this->material?->name,
                'base_unit' => $this->material?->base_unit,
                'price_per_base_unit' => $this->material?->price_per_base_unit,
            ],
            'type' => $this->type,
            'amount' => $this->amount,
            'description' => $this->description,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
        ];
    }
}
