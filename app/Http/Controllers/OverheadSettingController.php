<?php

namespace App\Http\Controllers;

use App\Models\OverheadSetting;
use Illuminate\Http\JsonResponse;


// Pengaturan overhead
class OverheadSettingController extends Controller
{
    // Ambil semua setting overhead
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
