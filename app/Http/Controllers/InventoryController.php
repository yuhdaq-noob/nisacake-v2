<?php

namespace App\Http\Controllers;

use App\Models\Material;
use Illuminate\View\View;


// Controller untuk halaman (gudang)
class InventoryController extends Controller
{
    // Menampilkan gudang
    public function index(): View
    {
        // Ambil semua data bahan baku
        $materials = Material::orderBy('current_stock', 'asc')->get();

        // Tampilkan view gudang beserta data materials
        return view('gudang', compact('materials'));
    }
}
