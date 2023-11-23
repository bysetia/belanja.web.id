<?php

namespace App\Models;


use App\Models\Store;
use App\Models\Review;
use App\Models\User;
use App\Models\GalleryReview;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReviewStoreUser extends Model
{
    protected $fillable = ['review', 'user_id', 'store_id', 'review_id'];

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }
    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }


    public function gallery()
    {
        return $this->hasMany(GalleryReview::class, 'review_id');
    }
}
