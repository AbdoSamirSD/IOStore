<?php

namespace App\Http\Controllers\Api\Admin\Categories;

use App\Http\Controllers\Controller;
use App\Models\SubCategory;
use App\Traits\FileUploadTrait;
use Illuminate\Http\Request;

class SubCategoriesController extends Controller
{
    use FileUploadTrait;
    public function index(Request $request)
    {
        $subCategories = SubCategory::when(
            request('main_category_id'),
            fn($query) => $query->where('main_category_id', request('main_category_id'))
        )
            ->when(request('name'), fn($query) => $query->whereTranslationLike('name', '%' . $request->name . '%'))
            ->latest()->paginate(perPage: 200);
        return response()->json(['subCategories' => $subCategories], 200);
    }

    public function store(Request $request)
    {
        $data =  $request->validate([
            'name' => 'required|unique:sub_category_translations,name',
            'main_category_id' => 'required|exists:main_categories,id',
            'icon' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:10048',
        ]);

        if ($request->hasFile('icon')) {
            $data['icon'] = $this->uploadFile($request, 'icon', path: '/uploads/sub_categories');
        }
        $subCategory =  SubCategory::create([
            'ar' => ['name' => $request->name],
            'en' => ['name' => $request->name],
            'main_category_id' => $request->main_category_id,
            'icon' => $data['icon'] ?? null
        ]);

        return response()->json([
            'message' => __('site.success'),
            'subCategory' => $subCategory
        ], 200);
    }

    public function update(Request $request, SubCategory $subCategory)
    {
        $data = $request->validate([
            'name' => 'required|unique:sub_category_translations,name,' . $subCategory->id . ',sub_category_id',
            'icon' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:10048',
        ]);
        if ($request->hasFile('icon')) {
            $data['icon'] = $this->uploadFile($request, 'icon', path: '/uploads/sub_categories');
        }
        $subCategory->update([
            'ar' => ['name' => $request->name],
            'en' => ['name' => $request->name],
            'icon' => $data['icon'] ?? $subCategory->icon
        ]);
        return response()->json([
            'message' => __('site.success'),
            'subCategory' => $subCategory
        ], 200);
    }

    public function destroy(SubCategory $subCategory)
    {
        $subCategory->delete();
        return response()->json(['message' => __('site.success')], 200);
    }
}
