<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

// Resource untuk format data log perubahan harga bahan baku
class MaterialPriceLogResource extends JsonResource
{
    // Mengubah data model menjadi array untuk response API
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'material' => [
                'id' => $this->material?->id,
                'name' => $this->material?->name,
            ],
            'old_price_per_base_unit' => $this->old_price_per_base_unit,
            'new_price_per_base_unit' => $this->new_price_per_base_unit,
            'base_unit' => $this->base_unit,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
        ];
    }
}
