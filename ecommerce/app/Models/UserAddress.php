<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Regency;

class UserAddress extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'address_one',
        'address_two',
        'provinces',
        'regencies',
        'zip_code',
        'country',
        'is_primary',
        'receiver_name', 
        'phone_number', 
    ];

      public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    
    
    // Set default value for 'is_primary'
    protected $attributes = [
        'is_primary' => 0,
    ];

    // Boot the model
    protected static function booted()
    {
        static::creating(function ($userAddress) {
            // Check if the user_id already has an address
            if (UserAddress::where('user_id', $userAddress->user_id)->exists()) {
                $userAddress->is_primary = 0; // Set is_primary to 0 if user already has an address
            } else {
                $userAddress->is_primary = 1; // Set is_primary to 1 if user does not have any address yet
            }
        });
    }
    
    
    public function regency()
    {
        return $this->belongsTo(Regency::class, 'regencies', 'name');
    }
}
