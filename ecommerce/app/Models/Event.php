<?php

namespace App\Models;

use App\Models\EventGallery;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Event extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'title',
        'description',
        'date',
        'time',
        'location',
        'poster',
    ];

    // ?  relasi ke galeri event
    public function galleries()
    {
        return $this->hasMany(EventGallery::class, 'events_id', 'id');
    }
    public function showEvent()
    {
        $totalEvent = Event::count();

        return view('welcome', compact('totalEvent'));
    }

   public function registeredUsers()
    {
        return $this->belongsToMany(User::class, 'user_event')->withTimestamps();
    }
    

}
