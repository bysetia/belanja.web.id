<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;  

use App\Models\User;
use App\Models\Product;
use App\Models\Courier;
use App\Models\TransactionItem;
use App\Models\Transaction;
use App\Models\Store;
use App\Models\UserAddress;

class Transaction extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'transactions';
    protected $foreignKey = 'product_id';

    
     protected $casts = [
        'created_at' => 'datetime',
    ];
    protected $fillable = [
        'users_id', 'products_id', 'quantity', 'total', 'status', 'payment_url','snap_token', 'shipping_cost', 'courier', 'user_address_id', 'product_name', 'product_price',
    ];

    public function getCreatedAttribute($value)
    {
        return Carbon::parse($value)->timestamp;
    }

    public function getUpdatedAttribute($value)
    {
        return Carbon::parse($value)->timestamp;
    }
      public function transaction()
    {
        return $this->belongsTo(Transaction::class, 'transactions_id');
    }

    // Relasi ke user
    public function user()
    {
        return $this->belongsTo(User::class, 'users_id', 'id');
    }

    // Relasi ke product
    // public function product()
    // {
    //     return $this->belongsTo(Product::class, 'products_id', 'id');
    // }
    
    public function product()
    {
        return $this->belongsTo(Product::class, 'products_id', 'id')->withDefault();
    }
    
     // Definisi relasi dengan model TransactionItem
    public function items()
    {
        return $this->hasMany(TransactionItem::class, 'transaction_id','id');
    }
       public function user_address()
    {
        return $this->belongsTo(UserAddress::class, 'user_address_id', 'id');
    }

    
        public function courier()
    {
        return $this->belongsTo(Courier::class, 'courier', 'id');
    }

     public function store()
    {
        return $this->belongsTo(Store::class, 'store_id', 'id');
    }
}
