<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'order_number',
        'sub_total',
        'delivery_fee',
        'discount',
        'total_cost',
        'promo_code_id',
        'status'
    ];
    protected $casts = [
        'sub_total' => 'float',
        'delivery_fee' => 'float',
        'discount' => 'float',
        'total_cost' => 'float',
        'promo_code_id' => 'integer',

    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function promoCode()
    {
        return $this->belongsTo(PromoCode::class);
    }
}
