<?php

namespace App\Models;

use App\Models\User;
use App\Models\Product;
use App\Models\GalleryReview;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'users_id', 'product_id', 'review', 'rate',
    ];

      public function user()
    {
        return $this->belongsTo(User::class, 'users_id', 'id');
    }
    
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }

     public function galleryReviews()
    {
        return $this->hasMany(GalleryReview::class);
    }
    
    
}
