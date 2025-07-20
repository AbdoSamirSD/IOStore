<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;

class MainCategory extends Model implements TranslatableContract
{
    use HasFactory, Translatable;
    protected $fillable = ['icon'];

    public $translatedAttributes = ['name'];

    protected $hidden = [
        'created_at',
        'updated_at',
        'translations',
    ];

    // relations
    public function subCategories()
    {
        return $this->hasMany(SubCategory::class);
    }
    public function products()
    {
        return $this->hasMany(Product::class);
    }

    // attributes

    public function getIconAttribute($value)
    {
        return $value ? asset($value) : null;
    }

    public function specifications()
    {
        return $this->belongsToMany(Specification::class, 'category_specifications');
    }
}
