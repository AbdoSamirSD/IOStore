<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;

class Product extends Model implements TranslatableContract
{
    use HasFactory, Translatable;

    protected $fillable = [
        'colors',
        'price',
        'supplier_price',
        'stock',
        'discount',
        'main_category_id',
        'sub_category_id',
    ];

    public $translatedAttributes = ['name', 'description', 'details', 'instructions',];
    protected $hidden = [
        'created_at',
        'updated_at',
        'translations',
    ];

    protected $appends = [
        'images',
        'is_favorite',
        'is_cart_item',
        'is_available',
    ];

    // relations
    public function images()
    {
        return $this->morphMany(ImageItem::class, 'imageable');
    }
    public function mainCategory()
    {
        return $this->belongsTo(MainCategory::class);
    }

    public function subCategory()
    {
        return $this->belongsTo(SubCategory::class);
    }
    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }
    public function cartItems()
    {
        return $this->hasMany(CartItem::class);
    }

    public function orders()
    {
        return $this->hasMany(OrderItem::class);
    }

    // attributes
    public function getDiscountAttribute($value)
    {
        return $value / 100;
    }
    public function getImagesAttribute()
    {
        return $this->images()->get();
    }

    public function getColorsAttribute($value)
    {
        return json_decode($value);
    }


    public function getIsFavoriteAttribute()
    {
        if (!auth()->check()) {
            return false;
        }

        return $this->favorites()
            ->where('user_id', auth()->id())
            ->where('is_favorite', true)
            ->exists();
    }

    public function getCartQuantityAttribute()
    {
        return $this->cartItems()
            ->where('user_id', auth()->id())
            ->sum('quantity');
    }
    public function getIsCartItemAttribute()
    {
        return $this->cartItems()
            ->where('user_id', auth()->id())
            ->exists();
    }
    public function getIsAvailableAttribute()
    {
        return $this->stock > 0;
    }
}
