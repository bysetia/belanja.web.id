<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TransactionItem;
use App\Models\Cart;
use App\Models\City;
use App\Models\Product;
use App\Helpers\ResponseFormatter;
use Illuminate\Support\Facades\Validator;

class TransactionItemController extends Controller
{

public function checkout(Request $request)
{
    // Validasi input
    $validator = Validator::make($request->all(), [
        'cart_id' => 'required|array',
        'cart_id.*' => 'exists:carts,id',
        'products' => 'required|array',
        'products.*.id' => 'required|exists:products,id',
        'products.*.quantity' => 'required|numeric|min:1',
    ]);

    // Jika validasi gagal, kembalikan pesan kesalahan
    if ($validator->fails()) {
        return ResponseFormatter::error($validator->errors(), 422);
    }

    $checkoutProducts = [];

    foreach ($request->cart_id as $index => $cartId) {
        // Ambil objek Cart berdasarkan cart_id dari request
        $cart = Cart::find($cartId);

        // Jika objek Cart tidak ditemukan, kembalikan pesan kesalahan
        if (!$cart) {
            return ResponseFormatter::error('Cart with ID ' . $cartId . ' not found', 404);
        }

        // Ambil data produk yang ingin di-checkout dari request
        $productData = $request->products[$index];

        // Ambil objek Product berdasarkan product_id dari request
        $product = Product::find($productData['id']);

        // Jika objek Product tidak ditemukan, kembalikan pesan kesalahan
        if (!$product) {
            return ResponseFormatter::error('Product with ID ' . $productData['id'] . ' not found', 404);
        }

        // Cek apakah stok produk mencukupi
        if ($product->quantity < $productData['quantity']) {
            return ResponseFormatter::error('Product with ID ' . $productData['id'] . ' does not have enough stock.', 400);
        }

        // Buat objek TransactionItem baru
        $transactionItem = new TransactionItem([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => $productData['quantity'],
            // Isi kolom-kolom lain yang diperlukan untuk detail transaksi jika ada
            // ...
        ]);

        // Simpan data transaksi ke database
        $transactionItem->save();

        // Tambahkan produk ke dalam array checkoutProducts
        $checkoutProducts[] = [
            'id' => $product->id,
            'name' => $product->name,
            'price' => $product->price,
            'quantity' => $productData['quantity'],
            'weight' => $product->weight,
            'picturePath' => $product->picturePath,
        ];
    }

    // Kembalikan response JSON dengan data checkoutProducts
    return response()->json([
        'meta' => [
            'code' => 200,
            'status' => 'success',
            'message' => 'Checkout products retrieved successfully',
        ],
        'data' => [
            'checkoutProducts' => $checkoutProducts,
        ],
    ]);
}


public function getCheckoutProducts(Request $request)
{
    $request->validate([
        'cart_id' => 'required|exists:transaction_items,cart_id',
    ]);

    // Get the checkout products based on the cart_id
    $checkoutProducts = TransactionItem::where('cart_id', $request->cart_id)
        ->with('product.store') // Load the 'product' relationship data along with 'store' relationship
        ->get();

    // Format the response data
    $formattedData = $checkoutProducts->map(function ($item) {
        $store = $item->product->store;
        $storeData = $store->toArray();
        
        // Fetch city_id based on regencies
        $cityId = $this->getCityIdByRegencyName($store->regencies);
        
        $storeData['city_id'] = $cityId;

        return [
            'id' => $item->product->id,
            'name' => $item->product->name,
            'price' => $item->product->price,
            'quantity' => $item->quantity,
            'picturePath' => $item->product->picturePath,
            'store' => $storeData,
        ];
    });

    // Return the response with the checkoutProducts data
    return response()->json([
        'meta' => [
            'code' => 200,
            'status' => 'success',
            'message' => 'Checkout products retrieved successfully',
        ],
        'data' => [
            'checkoutProducts' => $formattedData,
        ],
    ]);
}

private function getCityIdByRegencyName($regencyName)
{
    $regency = City::where('title', $regencyName)->first();

    if ($regency) {
        return $regency->city_id;
    } else {
        return null;
    }
}



}
