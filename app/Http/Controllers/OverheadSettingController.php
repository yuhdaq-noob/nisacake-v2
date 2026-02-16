<?php

namespace App\Http\Controllers;

use App\Models\OverheadSetting;
use Illuminate\Http\JsonResponse;


// Controller untuk pengaturan overhead
class OverheadSettingController extends Controller
{
    // Mengambil semua setting overhead yang dipakai untuk perhitungan HPP
	public function index(): JsonResponse
	{
	    $settings = OverheadSetting::query()
	        ->orderBy('key')
	        ->get();

	    // Gunakan Resource untuk formatting
	    return response()->json(\App\Http\Resources\OverheadSettingResource::collection($settings));
	}

    // ...existing code...
}
