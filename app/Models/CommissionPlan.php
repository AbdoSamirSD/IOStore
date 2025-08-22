<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommissionPlan extends Model
{
    use HasFactory;
    protected $fillable = [
        'vendor_id',
        'product_category_id',
        'commission_type',
        'fixed_value',
    ];

    public function vendor(){
        return $this->belongsTo(Vendor::class, 'vendor_id', 'id');
    }

    public function maincategory(){
        return $this->belongsTo(MainCategory::class);
    }

    public function ranges(){
        return $this->hasMany(CommissionRange::class);
    }
}
