<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    use HasFactory;

    protected $fillable = [
        'users_id', 'products_id', 'quantity'
    ];

    public function product()
    {
        return $this->hasOne(Product::class, 'id', 'products_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'users_id', 'id');
    }

    public static function addToCart($userId, $productId)
    {
        $cartItem = Cart::where('users_id', $userId)
            ->where('products_id', $productId)
            ->first();

        if ($cartItem) {
            // Jika item sudah ada di keranjang, tingkatkan jumlahnya
            $cartItem->increment('quantity');
        } else {
            // Jika item belum ada di keranjang, tambahkan sebagai item baru
            $cartItem = Cart::create([
                'users_id' => $userId,
                'products_id' => $productId,
                'quantity' => 1
            ]);
        }

        // Load relasi user dan product pada cartItem
        $cartItem->load('user', 'product');

        $responseData = [
            'message' => 'Item added to cart',
            'data' => [
                'cart' => $cartItem,
                'user' => $cartItem->user,
                'product' => $cartItem->product,
            ]
        ];

        return response()->json($responseData, 200);
    }





    public static function removeFromCart($userId, $productId)
    {
        $cartItem = Cart::where('users_id', $userId)
            ->where('products_id', $productId)
            ->first();

        if ($cartItem) {
            if ($cartItem->quantity > 1) {
                // Jika jumlah item lebih dari 1, kurangi jumlahnya
                $cartItem->decrement('quantity');
            } else {
                // Jika jumlah item 1, hapus item dari keranjang
                $cartItem->delete();
            }
        }

        return response()->json(['message' => 'Item removed from cart'], 200);
    }

    public static function deleteCart($userId)
    {
        Cart::where('users_id', $userId)->delete();

        return response()->json(['message' => 'All items in cart have been deleted'], 200);
    }
}
