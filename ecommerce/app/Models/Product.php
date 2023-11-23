<?php

namespace App\Models;

use App\Models\User;
use App\Models\Store;
use App\Models\Review;
use App\Models\ProductGallery;
use App\Models\ProductCategory;
use App\Models\TransactionItem;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Storage;

class Product extends Model
{
    use HasFactory, SoftDeletes;
    protected $dates = ['deleted_at'];

    protected $fillable = [
        'name', 'description', 'quantity', 'price', 'category_id', 'user_id', 'slug',
        'rate', 'picturePath', 'product_origin', 'product_material', 'weight', 'sold_quantity', 'sku',  'kondisi_produk', 'store_id', 'photo1',
        'photo2', 'photo3','photo4',
    ];


    public function getCreatedAttribute($value)
    {
        return Carbon::parse($value)->timestamp;
    }

    public function getUpdatedAttribute($value)
    {
        return Carbon::parse($value)->timestamp;
    }

    public function toArray()
    {
        $toArray = parent::toArray();
        $toArray['id'] = $this->id;
        $toArray['name'] = $this->name;
        $toArray['description'] = $this->description;
        $toArray['price'] = $this->price;
        $toArray['quantity'] = $this->quantity;
        $toArray['slug'] = $this->slug;
        $toArray['picturePath'] = $this->picturePath;
        $toArray['category_id'] = $this->category_id;
        $toArray['user_id'] = $this->user_id;
        $toArray['updated_at'] = $this->updated_at;
        $toArray['created_at'] = $this->created_at;
        return $toArray;
    }

    // public function getPicturePathAttribute($value)
    // {
    //   return 'https://belanja.penuhmakna.co.id' . Storage::url($value);
    // }

    // ?  relasi ke galeri product
    public function galleries()
    {
        return $this->hasMany(ProductGallery::class, 'products_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // ?  relasi ke galeri category
    public function category()
    {
        // ? kebalikan dari relasi 
        return $this->belongsTo(ProductCategory::class, 'category_id', 'id');
    }
    
    public function store()
    {
        return $this->belongsTo(Store::class, 'store_id', 'id');
    }

    
    // Definisi relasi dengan model TransactionItem
    public function transactions()
    {
        return $this->hasMany(TransactionItem::class, 'product_id');
    }
    
    public function reviews()
    {
        return $this->hasMany(Review::class);
    }
}
