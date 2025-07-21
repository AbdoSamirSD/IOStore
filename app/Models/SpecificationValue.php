<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SpecificationValue extends Model
{
    use HasFactory;

    protected $table = 'specification_values';
    protected $fillable = ['specification_id', 'value'];
    public function specification()
    {
        return $this->belongsTo(Specification::class);
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_specification_values');
    }
}
