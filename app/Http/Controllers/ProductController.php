<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProductCollection;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index(Request $request)
{
    try {
        $query = Product::with(['category', 'promotions']);

        // Filter berdasarkan kategori
        if ($request->query('category_id')) {
            $query->where('category_id', $request->query('category_id'));
        }

        // Filter berdasarkan harga
        if ($request->query('sort_by_price')) {
            $query->orderBy('price', $request->query('sort_by_price') == 1 ? 'asc' : 'desc');
        }

        // Filter berdasarkan nama produk
        if ($request->query('name')) {
            $query->where('name', 'like', '%' . $request->query('name') . '%');
        }

        // Filter berdasarkan rentang harga
        if ($request->query('min_price') || $request->query('max_price')) {
            $minPrice = is_numeric($request->query('min_price')) ? $request->query('min_price') : 0;
            $maxPrice = is_numeric($request->query('max_price')) ? $request->query('max_price') : 100000000;

            $query->whereBetween('price', [$minPrice, $maxPrice]);
        }

        // Cek tipe respons (array atau paginasi)
        if ($request->query('response_type') === 'array') {
            $products = $query->get(); // Mengambil semua data sebagai array
            return successResponse($products->toArray(), 'Produk berhasil diambil dalam bentuk array', 200);
        }

        // Default: Paginasi
        $products = $query->paginate(10);
        return successResponse(new ProductCollection($products), 'Produk berhasil diambil', 200);
    } catch (\Exception $e) {
        return errorResponse(null, $e->getMessage(), 404);
    }
}


    public function show($id)
    {
        try {
            $product = Product::with(['category', 'promotions', 'reviews'])->findOrFail($id);
            return new ProductResource($product);
        } catch (\Exception $e) {
            return errorResponse(null, $e->getMessage(), $e->getCode());
        }
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required',
                'price' => 'required|numeric',
                'stock' => 'required|numeric',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif',
                'category_id' => 'required|exists:categories,id',
            ]);

            $imagePath = null;
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('products', 'public');
            }

            $product = Product::create([
                'name' => $request->name,
                'product_code' => $request->product_code,
                'description' => $request->description,
                'price' => $request->price,
                'stock' => $request->stock,
                'category_id' => $request->category_id,
                'image' => $imagePath,
            ]);
            return successResponse($product, 'Produk berhasil ditambahkan', 201);
        } catch (\Exception $e) {
            return errorResponse(null, $e->getMessage(), 404);
        }
    }


    // ERROR
    // public function update(Request $request, $id)
    // {
    //     $request->validate([
    //         'name' => 'required',
    //         'price' => 'required|numeric',
    //         'category_id' => 'required|exists:categories,id',
    //         'image' => 'nullable|image|mimes:jpeg,png,jpg,gif',
    //         'stock' => 'required|numeric',
    //     ]);

    //     try {
    //         $product = Product::findOrFail($id);

    //         $product->update($request->all());
    //         return successResponse($product, 'Produk berhasil diupdate', 200);
    //     } catch (\Exception $e) {
    //         return errorResponse(null, $e->getMessage(), 404);
    //     }
    // }

    public function destroy($id)
    {
        try {
            $product = Product::findOrFail($id);
            $product->delete();
            return successResponse($product, 'Produk berhasil dihapus', 200);
        } catch (\Exception $e) {
            return errorResponse(null, $e->getMessage(), $e->getCode());
        }
    }
}
