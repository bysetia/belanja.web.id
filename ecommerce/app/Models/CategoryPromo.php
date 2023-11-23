<?php

namespace App\Models;

use App\Models\ProductCategory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CategoryPromo extends Model
{
    use HasFactory;
    public $table = "category_promo";

    protected $fillable = ['poster', 'banner', 'category_id'];

    public function category()
    {
        return $this->belongsTo(ProductCategory::class);
    }
}
