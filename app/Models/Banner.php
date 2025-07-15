<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    use HasFactory;
    protected $fillable = ['title', 'image_path', 'link', 'product_id', 'type'];

    protected $hidden = [
        'title',
        'link',
        'product_id',

        'created_at',
        'updated_at'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function getImagePathAttribute($value)
    {
        return $value ? asset($value) : null;
    }

}
