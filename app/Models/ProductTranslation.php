<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductTranslation extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = ['name', 'description', 'details', 'instructions', 'locale'];

    public function getInstructionsAttribute($value)
    {
        return json_decode($value);
    }
}
