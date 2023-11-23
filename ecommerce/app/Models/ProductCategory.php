<?php

namespace App\Models;

use App\Models\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProductCategory extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name', 'photo', 'slug', 'banner'
    ];

    // ? relasi ke products one to many
    public function products()
    {
        return $this->hasMany(Product::class, 'category_id', 'id');
    }
}
