<?php

namespace App\Models;

use App\Models\User;
use App\Models\Event;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserEvent extends Model
{
    protected $table = 'user_event';
    // Tambahkan atribut-atribut khusus jika diperlukan
    protected $fillable = ['id', 'users_id', 'events_id'];

    // Hubungan dengan model User
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Hubungan dengan model Event
    public function event()
    {
        return $this->belongsTo(Event::class);
    }
}
