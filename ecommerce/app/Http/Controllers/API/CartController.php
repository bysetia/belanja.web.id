<?php

namespace App\Http\Controllers\API;

use App\Models\Cart;
use Illuminate\Support\Facades\Auth;
use App\Models\Product;
use App\Helpers\ResponseFormatter;
use App\Models\User;
use App\Models\City;
use Illuminate\Http\Request;
use App\Models\Store; 
use App\Http\Controllers\Controller;

class CartController extends Controller
{
   public function addToCart(Request $request)
{
    $user = Auth::user();
    $productId = $request->input('products_id');
    $quantity = $request->input('quantity', 1); // Default to 1 if no quantity is provided in the request

    Cart::addToCart($user->id, $productId, $quantity);

    // Update the quantity in the cart data with the requested quantity
    $cartItem = Cart::where('users_id', $user->id)
                    ->where('products_id', $productId)
                    ->first();

    if ($quantity !== 1) {
        $cartItem->quantity = $quantity;
        $cartItem->save();
    }

    // Retrieve cart data for the logged-in user
    $cartData = Cart::where('users_id', $user->id)->get();

    // Get an array of product IDs from the cart data
    $productIds = $cartData->pluck('products_id')->toArray();

    // Fetch products related to the product IDs
    $products = Product::whereIn('id', $productIds)->get();

    // Format cart data to include product information
    $formattedCartData = $cartData->map(function ($cartItem) use ($products) {
        $product = $products->firstWhere('id', $cartItem->products_id);

        return [
            'id' => $cartItem->id,
            'product' => $product,
            'quantity' => $cartItem->quantity,
            'created_at' => $cartItem->created_at,
            'updated_at' => $cartItem->updated_at,
        ];
    });

    // Reorder the formatted cart data to have the newest items at the top
    $formattedCartData = $formattedCartData->reverse()->values();

    return ResponseFormatter::success([
        'cart' => $formattedCartData->toArray(),
    ], 'Item added to cart');
}


    public function removeFromCart(Request $request)
    {
        $userId = $request->input('users_id');
        $productId = $request->input('products_id');
        $quantity = $request->input('quantity');
    
        // Periksa apakah item keranjang ada
        $cartItem = Cart::where('users_id', $userId)
                        ->where('products_id', $productId)
                        ->first();
    
        if (!$cartItem) {
            return ResponseFormatter::error(null, 'Item not found in cart', 404);
        }
    
        // Periksa apakah kuantitas yang diminta lebih besar dari 0
        if ($quantity <= 0) {
            return ResponseFormatter::error(null, 'Invalid quantity', 422);
        }
    
        // Update kuantitas produk dalam keranjang
        $cartItem->quantity = $quantity;
        $cartItem->save();
    
        // Ambil kembali item-item keranjang yang terbaru
        $cartItems = Cart::where('users_id', $userId)->get();
    
        if ($cartItems->isEmpty()) {
            return ResponseFormatter::success(null, 'Cart is empty');
        }
    
        $productIds = $cartItems->pluck('products_id');
        $products = Product::whereIn('id', $productIds)->get();
    
        $formattedProducts = $products->map(function ($product) use ($cartItems) {
            $cartItem = $cartItems->firstWhere('products_id', $product->id);
            $quantity = $cartItem ? $cartItem->quantity : 0;
    
            return [
                'product' => $product,
                'quantity' => $quantity,
            ];
        });
    
        return ResponseFormatter::success($formattedProducts, 'Cart quantity updated successfully');
    }

    public function deleteCart($id)
    {
          $cart = Cart::find($id);

    if (!$cart) {
        return ResponseFormatter::error(null, 'Cart item not found', 404);
    }

    $cart->delete();

    return ResponseFormatter::success(null, 'Cart item has been deleted');
    }
    
    public function getAllCartItems()
    {
        $user = Auth::user();
    
        $cartItems = Cart::where('users_id', $user->id)->latest()->get();
    
        if ($cartItems->isEmpty()) {
            return ResponseFormatter::success([
                'cartItems' => null,
            ], 'Cart is empty');
        }
    
        $productIds = $cartItems->pluck('products_id');
        $products = Product::whereIn('id', $productIds)->with('store')->get(); // Include 'store' relation
    
        $formattedCartItems = $cartItems->map(function ($cartItem) use ($products) {
            $product = $products->firstWhere('id', $cartItem->products_id);
            
            $productCityId = null;
            if ($product && $product->store && $product->store->regencies) {
                $productCityId = $this->getCityIdByRegencyName($product->store->regencies);
            }
            return [
                'id' => $cartItem->id,
                'product' => $product,
                'quantity' => $cartItem->quantity,
                'created_at' => $cartItem->created_at,
                'updated_at' => $cartItem->updated_at,
                'city_id' => $productCityId,
            ];
        });
    
        return ResponseFormatter::success([
            'cartItems' => $formattedCartItems->toArray(),
        ], 'All cart items retrieved');
    }
    
    private function getCityIdByRegencyName($regencyName)
    {
        $city = City::where('title', $regencyName)->first();
    
        if ($city) {
            return $city->city_id;
        } else {
            return null;
        }
    }
}
