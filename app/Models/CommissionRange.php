<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommissionRange extends Model
{
    use HasFactory;

    protected $fillable = [
        'plan_name',
        'commission_plan_id',
        'min_value',
        'max_value',
        'percentage',
    ];

    public function commissionPlan()
    {
        return $this->belongsTo(CommissionPlan::class);
    }
}
