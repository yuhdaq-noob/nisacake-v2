<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;


// Controller untuk manajemen produk
class ProductController extends Controller
{
    // Mengambil semua produk beserta bahan bakunya
    public function index(): AnonymousResourceCollection
    {
        $products = Product::with('materials')->get(); // Ambil produk beserta relasi materials
        return ProductResource::collection($products); // Kembalikan dalam bentuk resource collection
    }

    // Menampilkan detail satu produk beserta bahan bakunya
    public function show(Product $product): ProductResource
    {
        return new ProductResource($product->load('materials'));
    }

    // Menyimpan produk baru ke database
    public function store(StoreProductRequest $request): JsonResponse
    {
        $product = Product::create($request->validated()); // Simpan produk baru
        return response()->json([
            'status' => 'success',
            'message' => 'Produk berhasil dibuat.',
            'data' => new ProductResource($product->load('materials')),
        ], 201);
    }

    // Mengupdate data produk (termasuk biaya overhead)
    public function update(UpdateProductRequest $request, Product $product): JsonResponse
    {
        $product->update($request->validated()); // Update data produk
        return response()->json([
            'status' => 'success',
            'message' => 'Produk berhasil diupdate.',
            'data' => new ProductResource($product->load('materials')),
        ], 200);
    }
}
