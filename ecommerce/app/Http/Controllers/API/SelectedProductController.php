<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helpers\ResponseFormatter;
use App\Models\Product;
use App\Models\SelectedProduct;
use Illuminate\Support\Facades\Auth;

class SelectedProductController extends Controller
{
    public function addToSelectedProduct(Request $request)
    {
        $productId = $request->input('product_id');
        $product = Product::findOrFail($productId);

        $user = Auth::user();

        // Cek apakah produk sudah ada di wishlist user
        $selected = SelectedProduct::where('users_id', $user->id)
            ->where('products_id', $productId)
            ->first();

        if ($selected) {
            return ResponseFormatter::error($selected, 'The product is already in the selected products.', 400);
        }

        // Tambahkan produk ke wishlist user
        $selected = new SelectedProduct();
        $selected->id = $selected->id; // Mengatur nilai id yang diinginkan
        $selected->users_id = $user->id;
        $selected->products_id = $productId;
        $selected->save();

        $selected->load('product'); // Mengambil informasi produk terkait

        return ResponseFormatter::success($selected, 'The product was successfully added to the selected product.');
    }

    public function deleteFromSelectedProduct($id)
    {
        $selected = SelectedProduct::findOrFail($id);
        $user = Auth::user();

        // Cek apakah wishlist tersebut dimiliki oleh user yang sedang login
        // if ($selected->users_id !== $user->id) {
        //     return ResponseFormatter::error('Unauthorized', null, 401);
        // }

        $selected->delete();

        return ResponseFormatter::success(null, 'Product successfully removed from selected product.');
    }

    // public function filterByCategoryAndAvailability(Request $request)
    // {
    //     $category = $request->input('category');
    //     $availability = $request->input('availability');

    //     // Query untuk filter berdasarkan kategori dan ketersediaan quantity
    //     $products = Product::where('category', $category);

    //     if ($availability === 'available') {
    //         $products->where('quantity', '>', 0);
    //     } elseif ($availability === 'unavailable') {
    //         $products->where('quantity', 0);
    //     }

    //     $filteredProducts = $products->get();

    //     return ResponseFormatter::success($filteredProducts);
    // }

    public function getAllSelectedProduct()
    {
        $selectedProducts = SelectedProduct::all();

        $selectedProducts->load('product'); // Mengambil informasi produk terkait

        return ResponseFormatter::success($selectedProducts, "Selected products retrieved successfully");
    }
}
