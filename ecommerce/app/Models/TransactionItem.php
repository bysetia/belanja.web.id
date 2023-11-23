<?php

namespace App\Models;

use App\Models\Product;
use App\Models\Transaction;
use App\Models\Cart;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TransactionItem extends Model
{

    use HasFactory;

    protected $fillable = [
        'cart_id',
        'product_id',
        'quantity',
        // Kolom-kolom lain yang diperlukan untuk detail transaksi
        // ...
    ];

    // Relasi ke tabel Cart
    public function cart()
    {
        return $this->belongsTo(Cart::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }

    public function transaction()
    {
        return $this->belongsTo(Transaction::class, 'transaction_id', 'id');
    }
}
