<?php

namespace App\Models;

use App\Models\Store;
use App\Models\Transaction;
use App\Models\Event;
use App\Models\Wishlist;
use App\Models\SelectedProduct;
use Laravel\Sanctum\HasApiTokens;
use Laravel\Jetstream\HasProfilePhoto;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{

    use HasApiTokens;
    use HasFactory;
    use HasProfilePhoto;
    use Notifiable;
    use TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'username',
        'phone',
        'address_one',
        'address_two',
        'provinces',
        'regencies',
        'zip_code',
        'country',
        // 'store_name',
        // 'categories_id',
        // 'store_status',
        'roles',
        'password',
        'reset_password_token',
        'reset_password_created_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'profile_photo_url',
    ];

    // ? relasi one to many transactions
    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'users_id', 'id');
    }

    /**
     * Get the store for the user.
     */
    public function store()
    {
        return $this->hasOne(Store::class);
    }
    public function wishlist()
    {
        return $this->hasMany(Wishlist::class, 'users_id');
    }

    public function selectedProduct()
    {
        return $this->hasMany(SelectedProduct::class, 'users_id');
    }

    public function user_addresses(): HasMany
    {
        return $this->hasMany(UserAddress::class);
    }

    public function hasVerifiedEmailAtTime($time)
    {
        return $this->email_verified_at && $this->email_verified_at->lt($time);
    }

    public function followers()
    {
        return $this->belongsToMany(Store::class, 'user_store_followers', 'user_id', 'store_id')
            ->withTimestamps();
    }

    public function registeredEvents()
    {
        return $this->belongsToMany(Event::class, 'user_event', 'user_id', 'event_id');
    }
}
