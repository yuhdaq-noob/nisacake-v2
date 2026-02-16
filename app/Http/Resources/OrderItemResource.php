<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

// Resource untuk format data item order
class OrderItemResource extends JsonResource
{
    // Mengubah data model menjadi array untuk response API
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_id' => $this->order_id,
            'product_id' => $this->product_id,
            'quantity' => $this->quantity,
            'price_per_unit' => $this->price_per_unit,
            'subtotal' => $this->quantity * $this->price_per_unit,
            'product' => new ProductResource($this->whenLoaded('product')),
        ];
    }
}
