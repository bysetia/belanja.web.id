<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Store;
use App\Models\Courier;


class SelectCourier extends Model
{
    protected $table = 'select_courier';

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function courier()
    {
        return $this->belongsTo(Courier::class, 'courier_id'); // Tambahkan 'courier_id' sebagai argumen
    }

    public function couriers()
    {
        return $this->belongsTo(Courier::class);
    }

    public function stores()
    {
        return $this->belongsToMany(Store::class, 'select_courier', 'select_courier_id', 'store_id')
            ->withPivot('store_id', 'courier_id'); // Add pivot fields
    }
}
