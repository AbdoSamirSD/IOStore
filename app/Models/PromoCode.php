<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PromoCode extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'discount',
        'type',
        'status',
        'start_date',
        'end_date',
        'uses',
        'max_uses',
    ];
    protected $hidden = [
        'created_at',
        'updated_at',
        'uses',
        'max_uses',
        'type',
        'status',
        'start_date',
        'end_date',
    ];
    protected $appends = ['is_valid'];
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function getIsValidAttribute()
    {

        if ($this->status == 'active' && $this->start_date <= now() && $this->end_date >= now() && $this->uses < $this->max_uses) {
            return true;
        }
        return false;
    }
    public function getDiscountAttribute($value)
    {
        return match ($this->type) {
            'fixed' => $value,
            default => $value / 100,
        };
    }

}
