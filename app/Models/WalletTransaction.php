<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WalletTransaction extends Model
{
    use HasFactory;
    protected $fillable = [
        'wallet_id',
        'type',
        'amount',
        'description',
        'status',
        'direction',
        'related_order_id',
        'created_at',
        'updated_at',
    ];

    public function wallet()
    {
        return $this->belongsTo(Wallet::class);
    }
}
