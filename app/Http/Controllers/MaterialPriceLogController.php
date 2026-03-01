<?php

namespace App\Http\Controllers;

use App\Http\Resources\MaterialPriceLogResource;
use App\Models\MaterialPriceLog;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class MaterialPriceLogController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $logs = MaterialPriceLog::with('material')
            ->latest()
            ->limit(50)
            ->get();
        return MaterialPriceLogResource::collection($logs);
    }
}
