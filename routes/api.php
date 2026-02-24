<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\MaterialController;
use App\Http\Controllers\MaterialPriceLogController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\OverheadSettingController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\StockController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Rute publik
Route::post('/login', [AuthController::class, 'login']);
// Catatan: Endpoint register belum dipakai oleh UI
Route::post('/register', [AuthController::class, 'register']);

// Rute yang dilindungi (butuh autentikasi)
Route::middleware('auth:sanctum')->group(function () {
    // Endpoint profil pengguna
    // Catatan: Endpoint ini belum dipakai oleh UI saat ini
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Manajemen Pesanan
    Route::post('/buat-pesanan', [OrderController::class, 'store']);
    Route::post('/jadwal-pesanan', [OrderController::class, 'preOrder']);
    Route::get('/jadwal-pesanan', [OrderController::class, 'getScheduledOrders']);
    Route::post('/orders/{order}/execute-preorder', [OrderController::class, 'executePreOrder']);
    // Catatan: Endpoint list orders belum dipakai oleh UI saat ini
    Route::get('/orders', [OrderController::class, 'index']);
    // Catatan: Endpoint detail order belum dipakai oleh UI saat ini
    Route::get('/orders/{order}', [OrderController::class, 'show']);
    Route::patch('/orders/{order}/complete', [OrderController::class, 'complete']);
    Route::patch('/orders/{order}/cancel', [OrderController::class, 'cancel']);

    // Manajemen Produk
    Route::get('/products', [ProductController::class, 'index']);
    Route::post('/products', [ProductController::class, 'store']);
    // Catatan: Endpoint detail produk belum dipakai oleh UI saat ini
    Route::get('/products/{product}', [ProductController::class, 'show']);
    Route::patch('/products/{product}', [ProductController::class, 'update']);

    // Manajemen Bahan
    Route::get('/materials', [MaterialController::class, 'index']);
    Route::patch('/materials/{material}/price', [MaterialController::class, 'updatePrice']);
    // Catatan: UI gudang memakai web route /materials/reduce (form submit), bukan API ini
    Route::post('/materials/reduce', [MaterialController::class, 'reduceStock']);
    Route::get('/materials/price-history', [MaterialPriceLogController::class, 'index']);

    // Pembuatan Laporan
    // Catatan: Laporan masih dalam pengembangan
    Route::get('/reports', [ReportController::class, 'index']);

    // Pengaturan Overhead
    Route::get('/overhead-settings', [OverheadSettingController::class, 'index']);

    // Manajemen Stok
    Route::post('/stocks/add', [StockController::class, 'store']);
    Route::get('/stocks/history', [StockController::class, 'index']);

    // Logout (keluar)
    Route::post('/logout', [AuthController::class, 'logout']);
});
