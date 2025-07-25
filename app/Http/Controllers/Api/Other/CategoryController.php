<?php

namespace App\Http\Controllers\Api\Other;

use App\Http\Controllers\Controller;
use App\Models\MainCategory;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    // public function categories(Request $request)
    // {
    //     $categories = MainCategory::with('subCategories')->get();
    //     return response()->json($categories);
    // }
    public function mainCategories()
    {
        $mainCategories = MainCategory::all();
        return response()->json($mainCategories);
    }

    // public function subCategories(Request $request)
    // {
    //     $request->validate(['main_category' => 'required|exists:main_categories,id']);
    //     $mainCategory = MainCategory::find($request->main_category);
    //     $categories = $mainCategory->subCategories;
    //     return response()->json($categories);
    // }
}
