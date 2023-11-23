<?php

namespace App\Models;

use App\Models\User;
use App\Models\Store;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserStoreFollower extends Model
{
    protected $table = 'user_store_followers';

    protected $fillable = ['user_id', 'store_id'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function store()
    {
        return $this->belongsTo(Store::class, 'store_id');
    }
}
