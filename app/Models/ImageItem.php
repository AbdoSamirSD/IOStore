<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImageItem extends Model
{
    use HasFactory;
    protected $fillable = ['image_path', 'imageable_id', 'imageable_type'];

    protected $hidden = [
        'image_path',
        'imageable_id',
        'imageable_type',
        'created_at',
        'updated_at',
    ];
    protected $appends = ['url'];


    public function imageable()
    {
        return $this->morphTo();
    }

    public function getUrlAttribute()
    {
        return asset($this->image_path);
    }
}
