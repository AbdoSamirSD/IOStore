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

    public function categories()
    {
        return $this->belongsToMany(MainCategory::class, 'category_specifications');
    }

    public function productValues()
    {
        return $this->hasMany(ProductSpecificationValue::class);
    }
}
