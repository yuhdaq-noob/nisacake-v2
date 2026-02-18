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

// Public routes
Route::post('/login', [AuthController::class, 'login']);
// FIXME: TIDAK DIPAKAI
// Endpoint register belum dipakai oleh UI (tidak ada halaman/JS register).
Route::post('/register', [AuthController::class, 'register']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // User profile endpoint
    // FIXME: TIDAK DIPAKAI
    // Endpoint ini belum dipakai oleh UI saat ini.
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Order Management
    Route::post('/buat-pesanan', [OrderController::class, 'store']);
    Route::post('/jadwal-pesanan', [OrderController::class, 'preOrder']);
    Route::get('/jadwal-pesanan', [OrderController::class, 'getScheduledOrders']);
    Route::post('/orders/{order}/execute-preorder', [OrderController::class, 'executePreOrder']);
    // FIXME: TIDAK DIPAKAI
    // Endpoint list orders belum dipakai oleh UI saat ini.
    Route::get('/orders', [OrderController::class, 'index']);
    // FIXME: TIDAK DIPAKAI
    // Endpoint detail order belum dipakai oleh UI saat ini.
    Route::get('/orders/{order}', [OrderController::class, 'show']);
    Route::patch('/orders/{order}/complete', [OrderController::class, 'complete']);

    // Product Management
    Route::get('/products', [ProductController::class, 'index']);
    Route::post('/products', [ProductController::class, 'store']);
    // FIXME: TIDAK DIPAKAI
    // Endpoint detail produk belum dipakai oleh UI saat ini.
    Route::get('/products/{product}', [ProductController::class, 'show']);
    Route::patch('/products/{product}', [ProductController::class, 'update']);

    // Material Management
    Route::get('/materials', [MaterialController::class, 'index']);
    Route::patch('/materials/{material}/price', [MaterialController::class, 'updatePrice']);
    // FIXME: TIDAK DIPAKAI
    // UI gudang memakai web route /materials/reduce (form submit), bukan API ini.
    Route::post('/materials/reduce', [MaterialController::class, 'reduceStock']);
    Route::get('/materials/price-history', [MaterialPriceLogController::class, 'index']);

    // Report Generation
    // FIXME: PERHITUNGAN
    Route::get('/reports', [ReportController::class, 'index']);

    // Overhead Settings
    Route::get('/overhead-settings', [OverheadSettingController::class, 'index']);

    // Stock Management
    Route::post('/stocks/add', [StockController::class, 'store']);
    Route::get('/stocks/history', [StockController::class, 'index']);

    // Logout
    Route::post('/logout', [AuthController::class, 'logout']);
});
