<?php

namespace App\Http\Controllers\Api\Vendor\Products;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MainCategory;

class CategoryController extends Controller
{
    public function categories()
    {
        $categories = MainCategory::get();
        return response()->json([
            'message' => 'Categories retrieved successfully',
            'data' => $categories->map(function ($category) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                ];
            })
        ]);
    }

    public function specifications($id)
    {

        $category = MainCategory::with(['specifications.values'])->findOrFail($id);

        $result = $category->specifications->map(function ($spec) {
            return [
                'name' => $spec->name,
                'values' => $spec->values->pluck('value')->toArray()
            ];
        });

        return response()->json([
            'status' => true,
            'message' => 'Specifications fetched successfully',
            'data' => $result
        ]);
    }
}