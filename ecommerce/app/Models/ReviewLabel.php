<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReviewLabel extends Model
{
    protected $table = 'review_labels';
    
    protected $fillable = [
        'label', 
    ];
}
