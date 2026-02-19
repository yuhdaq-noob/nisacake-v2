<?php

namespace App\Http\Controllers;

use App\Models\Material;
use Illuminate\View\View;


// Controller untuk halaman (gudang)
class InventoryController extends Controller
{
   public function index(): View
    {
        $materials = Material::orderBy('current_stock', 'asc')->get();

        return view('gudang', compact('materials'));
    }
}
