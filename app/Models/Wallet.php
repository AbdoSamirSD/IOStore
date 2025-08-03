<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Wallet extends Model
{
    use HasFactory;
    protected $fillable = [
        'vendor_id',
        'balance',
        'pending_balance',
        'withdrawn_amount',
        'total_earnings',
        'total_withdrawn',
        'total_refunded',
        'total_commission',
        'created_at',
        'updated_at',
    ];

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function transactions()
    {
        return $this->hasMany(WalletTransaction::class);
    }
}
