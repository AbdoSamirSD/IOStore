<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Specification extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'type', 'options'];
    protected $casts = [
        'options' => 'array',
    ];

    public function maincategories()
    {
        return $this->belongsToMany(MainCategory::class, 'category_specifications');
    }

    public function values()
    {
        return $this->hasMany(SpecificationValue::class);
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_specification_values');
    }
}
