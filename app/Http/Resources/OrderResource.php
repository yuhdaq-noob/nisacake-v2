<?php

// FIXME: PERHITUNGAN

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

// Resource untuk format data order/pesanan
class OrderResource extends JsonResource
{
    // Mengubah data model menjadi array untuk response API
    public function toArray(Request $request): array
    {
        $items = $this->whenLoaded('items');

        // Siapkan string ringkas produk + qty untuk halaman laporan
        $productsLabel = null;
        if ($items) {
            $productsLabel = $items
                ->map(function ($item) {
                    $name = $item->product?->name ?? 'Produk Dihapus';

                    return $name.' ('.$item->quantity.')';
                })
                ->implode(', ');
        }

        return [
            'id' => $this->id,
            'customer_name' => $this->customer_name,
            // Alias agar kompatibel dengan front-end lama
            'customer' => $this->customer_name,
            // FIXME: TIDAK DIPAKAI
            // order_date tidak dipakai oleh UI saat ini (frontend menggunakan field 'date' dari created_at).
            'order_date' => $this->order_date?->format('Y-m-d H:i:s'),
            // Tanggal yang diformat untuk UI (Asia/Jakarta)
            'date' => $this->created_at?->timezone('Asia/Jakarta')->format('d M Y H:i'),
            'status' => $this->status,
            'total_price' => $this->total_price,
            // Alias agar kompatibel dengan front-end lama
            'total_omzet' => $this->total_price,
            'total_hpp' => $this->total_hpp,
            // FIXME: PERHITUNGAN
            'profit' => $this->total_price - $this->total_hpp,
            'items_count' => $items ? $items->count() : null,
            'products' => $productsLabel,
            'items' => OrderItemResource::collection($items),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
