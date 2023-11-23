<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Helpers\ResponseFormatter;
use App\Models\Product;
use App\Models\Wishlist;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;


class WishlistController extends Controller
{
    public function addToWishlist(Request $request)
    {
        $productId = $request->input('product_id');
        $product = Product::findOrFail($productId);

        $user = Auth::user();

        // Cek apakah produk sudah ada di wishlist user
        $wishlist = Wishlist::where('users_id', $user->id)
            ->where('products_id', $productId)
            ->first();

        if ($wishlist) {
            return ResponseFormatter::error($wishlist, 'The product is already in the wishlist.', 400);
        }

        // Tambahkan produk ke wishlist user
        $wishlist = new Wishlist();
        $wishlist->users_id = $user->id;
        $wishlist->products_id = $productId;
        $wishlist->save();

        $wishlist->load('product'); // Mengambil informasi produk terkait

        return ResponseFormatter::success($wishlist, 'The product was successfully added to the wishlist.');
    }

    public function deleteFromWishlist($id)
    {
        $wishlist = Wishlist::findOrFail($id);
        $user = Auth::user();

        // Cek apakah wishlist tersebut dimiliki oleh user yang sedang login
        // if ($wishlist->users_id !== $user->id) {
        //     return ResponseFormatter::error('Unauthorized', null, 401);
        // }

        $wishlist->delete();

        return ResponseFormatter::success(null, 'Product successfully removed from wishlist.');
    }

    public function filterByCategoryAndAvailability(Request $request)
    {
        $category = $request->input('category');
        $availability = $request->input('availability');

        // Query untuk filter berdasarkan kategori dan ketersediaan quantity
        $products = Product::where('category', $category);

        if ($availability === 'available') {
            $products->where('quantity', '>', 0);
        } elseif ($availability === 'unavailable') {
            $products->where('quantity', 0);
        }

        $filteredProducts = $products->get();

        return ResponseFormatter::success($filteredProducts);
    }

    public function getAllWishlist()
    {
        $user = Auth::user();
        $wishlist = $user->wishlist()->latest()->get();

        $wishlist->load('product'); // Mengambil informasi produk terkait

        return ResponseFormatter::success($wishlist, "Wishlist retrieved successfully");
    }
}
