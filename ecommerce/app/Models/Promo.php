<?php

namespace App\Models;

use App\Models\Product;
use App\Models\CategoryPromo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Promo extends Model
{
    use HasFactory;

    protected $fillable = [
        'start_date', 'end_date', 'product_id', 'after_promo', 'persen_promo', 'status',
    ];
    

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    
    public function toArray()
    {
       $array = parent::toArray();

        // Dapatkan semua atribut dari model Product
        $productAttributes = $this->product->toArray();

        // Gabungkan array atribut dari model Product ke dalam array Promo
        $array = array_merge($array, $productAttributes);

        return $array;
    }
    
    public function categoryPromo()
    {
        return $this->belongsTo(CategoryPromo::class);
    }

}
