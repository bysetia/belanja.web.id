<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class  EventGallery extends Model
{
    use HasFactory , SoftDeletes;

    protected $fillable = [
        'events_id', 'url'
    ];

    // ? laravel mutator convert field url
    public function getUrlAttribute($url)
    {
        // ? full url dari gamabar di api
        return config('app.url') . Storage::url($url);
    }
}
