<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;

class  SubCategory extends Model implements TranslatableContract
{
    use HasFactory, Translatable;

    protected $fillable = ['icon', 'main_category_id'];
    public $translatedAttributes = ['name'];

    protected $hidden = [
        'created_at',
        'updated_at',
        'translations',
    ];

    // relations
    public function mainCategory()
    {
        return $this->belongsTo(MainCategory::class);
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
}
