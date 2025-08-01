<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderStatusLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'source',
        'updated_by',
        'status',
        'status_changed_at',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
