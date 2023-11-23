<?php

namespace App\Models;

use App\Models\Review;
use App\Models\User;
use App\Models\Product;
use App\Models\City;
use App\Models\Courier;
use App\Models\SelectCourier;
use App\Models\ReviewStoreUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Store extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'stores';

    protected $casts = [
        'operating_hours' => 'array',
    ];
    protected $dates = ['open_time', 'close_time'];
    public function days(): BelongsToMany
    {
        return $this->belongsToMany(Day::class, 'operational_day')
            ->as('operational_day')
            ->withPivot('id', 'open_time', 'close_time');
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'logo',
        'description',
        'address_one',
        'address_two',
        'provinces',
        'regencies',
        'zip_code',
        'country',
        'status',
        'user_id',
        'saldo',
    ];

    /**
     * Get the user that owns the store.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
     public function reviews()
    {
        return $this->hasMany(Review::class);
    }
    public function reviewStoreUsers()
    {
        return $this->hasMany(ReviewStoreUser::class);
    }
    
    public function products()
    {
        return $this->hasMany(Product::class);
    }
    public function followers()
    {
        return $this->belongsToMany(User::class, 'user_store_followers', 'store_id', 'user_id');
    }
    
    public function selectCouriers()
    {
        return $this->belongsToMany(SelectCourier::class, 'select_courier', 'store_id', 'courier_id')
            ->withPivot('store_id', 'courier_id');
    }
     public function selectCourierss()
    {
        return $this->hasMany(SelectCourier::class);
    }
    
     public function city()
    {
        return $this->belongsTo(City::class, 'city_id', 'title');
    }
}
