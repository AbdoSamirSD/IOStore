<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductSpecificationValue extends Model
{
    use HasFactory;

    protected $fillable = ['value', 'product_id', 'specification_id'];
    protected $casts = [
        'value' => 'array',
    ];
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    public function specification()
    {
        return $this->belongsTo(Specification::class);
    }
}
