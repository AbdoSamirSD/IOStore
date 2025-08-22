<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;

class CategorySpecification extends Model
{
    use HasFactory;

    protected $table = 'category_specifications';
    protected $fillable = [
        'category_id',
        'specification_id',
    ];

    public function mainCategory()
    {
        return $this->belongsTo(MainCategory::class, 'category_id');
    }

    public function specification()
    {
        return $this->belongsTo(Specification::class, 'specification_id');
    }
}
