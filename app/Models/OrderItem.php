<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'product_id',
        'name',
        'main_category_name',
        'image',
        'quantity',
        'price',
    ];

    protected $casts = [
        'product_id' => 'integer',
        'quantity' => 'integer',
        'price' => 'float',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function vendor(){
        return $this->belongsTo(Vendor::class);
    }

}
