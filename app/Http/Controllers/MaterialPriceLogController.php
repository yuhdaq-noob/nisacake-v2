<?php

namespace App\Http\Controllers;

use App\Http\Resources\MaterialPriceLogResource;
use App\Models\MaterialPriceLog;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;


// Controller untuk histori perubahan harga bahan baku
class MaterialPriceLogController extends Controller
{
    // Mengambil 50 histori perubahan harga terakhir
    public function index(): AnonymousResourceCollection
    {
        $logs = MaterialPriceLog::with('material')
            ->latest()
            ->limit(50)
            ->get();

        // Kembalikan dalam bentuk resource collection
        return MaterialPriceLogResource::collection($logs);
    }
}
