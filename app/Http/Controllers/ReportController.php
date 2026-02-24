<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Exports\LaporanExport;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Maatwebsite\Excel\Facades\Excel;


// Controller untuk laporan (export dan tampilan data order)
class ReportController extends Controller
{
    // Mengambil semua order beserta itemnya untuk keperluan laporan
    public function index(): AnonymousResourceCollection
    {
        // Ambil semua order yang sudah COMPLETED (baik immediate maupun pre-order yang sudah dibayar)
        // Pre-order yang belum dibayar akan ditampilkan di bagian jadwal pesanan
        $orders = Order::with('items.product')
            ->where('status', OrderStatus::COMPLETED->value)
            ->orderBy('created_at', 'desc')
            ->get();

        // Kembalikan dalam bentuk resource collection
        return OrderResource::collection($orders);
    }

    // Export data order ke Excel atau PDF
    public function export(Request $request)
    {
        $format = $request->query('format', 'excel'); // Format export (excel/pdf)
        // Catatan: period & search parameter sudah dibaca, belum diterapkan untuk filter query export
        $period = $request->query('period', 'all'); // Periode filter (belum dipakai)
        $search = $request->query('search', '');    // Kata kunci pencarian (belum dipakai)

        // Ambil data order beserta relasi items dan produk yang sudah COMPLETED
        $orders = Order::with('items.product')
            ->where('status', OrderStatus::COMPLETED->value)
            ->orderBy('created_at', 'desc')
            ->get();


        // Export ke PDF jika format=pdf, selain itu ke Excel
        if ($format === 'pdf') {
            $pdf = Pdf::loadView('exports.laporan_pdf', [
                'orders' => $orders,
            ]);

            return $pdf->download('laporan.pdf');
        } else {
            return Excel::download(new LaporanExport($orders), 'laporan.xlsx');
        }
    }
}
