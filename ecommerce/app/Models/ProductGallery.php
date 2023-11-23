<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProductGallery extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'products_id', 'url', 'is_featured', 'selectProduct',
    ];

    public function toArray()
    {
        $array = parent::toArray();
        $array['user_id'] = $this->user_id; // Menambahkan user_id ke dalam array data
        return $array;
    }

    // ? laravel mutator convert field url
    public function getUrlAttribute($url)
    {
        // ? full url dari gamabar di api
        return config('app.url') . Storage::url($url);
    }

    // Menggunakan penamaan kunci asing kustom
    public function product()
    {
        return $this->belongsTo(Product::class, 'products_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Mengubah URL gambar menjadi picturePath
    public function getPicturePathAttribute($url)
    {
        // Mengembalikan URL lengkap gambar dalam API
        return config('app.url') . Storage::url($url);
    }
}
