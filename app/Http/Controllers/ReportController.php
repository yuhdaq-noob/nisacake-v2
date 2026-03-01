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

class ReportController extends Controller
{
    // Ambil data order COMPLETED untuk laporan
    public function index(): AnonymousResourceCollection
    {
        $orders = Order::with('items.product')
            ->where('status', OrderStatus::COMPLETED->value)
            ->orderBy('created_at', 'desc')
            ->get();

        return OrderResource::collection($orders);
    }

    // Export order ke Excel/PDF
    public function export(Request $request)
    {
        $format = $request->query('format', 'excel');

        $orders = Order::with('items.product')
            ->where('status', OrderStatus::COMPLETED->value)
            ->orderBy('created_at', 'desc')
            ->get();

        if ($format === 'pdf') {
            $pdf = Pdf::loadView('exports.laporan_pdf', [
                'orders' => $orders,
            ]);

            return $pdf->download('laporan.pdf');
        }

        return Excel::download(new LaporanExport($orders), 'laporan.xlsx');
    }
}

