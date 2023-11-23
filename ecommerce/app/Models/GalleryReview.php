<?php

namespace App\Models;

use App\Models\Review;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GalleryReview extends Model
{
    use HasFactory;

    protected $table = 'galleryreviews';
    protected $fillable = [
        'review_id',
        'image_path',
        'image_path_2',
        'image_path_3',
        'image_path_4',
    ];

    public function review()
    {
        return $this->belongsTo(Review::class);
    }
}
