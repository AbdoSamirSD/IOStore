<?php

namespace App\Http\Controllers\Api\Admin\Categories;

use App\Http\Controllers\Controller;
use App\Models\MainCategory;
use Illuminate\Http\Request;
use App\Traits\FileUploadTrait;
use Validator;

class MainCategoriesController extends Controller
{
    use FileUploadTrait;

    public function index()
    {
        $admin = auth('admin')->user();
        if (!$admin) {
            return response()->json(['message' => "Unauthorized"] , 401);
        }
        $mainCategories = MainCategory::latest()->paginate(perPage: 20);
        $count = $mainCategories->total();
        return response()->json([
            'message' => 'Main Categories retrieved successfully',
            'mainCategories' => $mainCategories,
            'count' => $count
        ], 200);
    }

    public function store(Request $request)
    {
        $admin = auth('admin')->user();
        if (!$admin) {
            return response()->json(['message' => "Unauthorized"] , 401);
        }

       $validator = Validator::make($request->all(), [
           'name' => 'required|string|max:255|unique:main_category_translations,name',
           'icon' => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
       ]);

       if ($validator->fails()) {
           return response()->json(['errors' => $validator->errors()], 422);
       }

        if ($request->hasFile('icon')) {
            $data['icon'] = $this->uploadFile($request, 'icon', path: '/uploads/main_categories');
        }
        $mainCategory = MainCategory::create([
            'en' => ['name' => $request->name],
            'icon' => $data['icon'] ?? null
        ])->load(['translations']
        );
        return response()->json(['message' => __('Main Category created successfully'), 'mainCategory' => $mainCategory], 200);
    }

    public function update(Request $request, $mainCategory)
    {
        $admin = auth('admin')->user();
        if (!$admin) {
            return response()->json(['message' => "Unauthorized"] , 401);
        }

        $mainCategory = MainCategory::find($mainCategory);
        if (!$mainCategory) {
            return response()->json(['message' => 'Main Category not found'], 404);
        }
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:main_category_translations,name,' . $mainCategory->id . ',main_category_id',
            'icon' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:10048',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        if ($request->hasFile('icon')) {
            $data['icon'] = $this->uploadFile($request, 'icon', path: '/uploads/main_categories');
        }
        $mainCategory->update([
            'en' => ['name' => $request->name],
            'icon' => $data['icon'] ?? $mainCategory->icon
        ]);
        return response()->json([
            'message' => __('Main Category updated successfully'),
            'mainCategory' => $mainCategory
        ], 200);
    }

    public function destroy($mainCategories)
    {
        $admin = auth('admin')->user();
        if (!$admin) {
            return response()->json(['message' => "Unauthorized"] , 401);
        }

        $mainCategory = MainCategory::find($mainCategories);
        if (!$mainCategory) {
            return response()->json(['message' => 'Main Category not found'], 404);
        }
        if ($mainCategory->icon) {
            $this->deleteFile($mainCategory->icon);
        }

        $mainCategory->delete();

        return response()->json(['message' => __('Main Category deleted successfully')], 200);
    }
}
