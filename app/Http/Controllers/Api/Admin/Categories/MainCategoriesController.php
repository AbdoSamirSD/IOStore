<?php

namespace App\Http\Controllers\Api\Admin\Categories;

use App\Http\Controllers\Controller;
use App\Models\MainCategory;
use Illuminate\Http\Request;
use App\Traits\FileUploadTrait;

class MainCategoriesController extends Controller
{
    use FileUploadTrait;

    public function index(Request $request)
    {
        $mainCategories = MainCategory::whereTranslationLike('name', '%' . $request->name . '%')->latest()->paginate(perPage: 50);
        return response()->json(['mainCategories' => $mainCategories], 200);
    }

    public function store(Request $request)
    {

        $data = $request->validate([
            'name' => 'required|unique:main_category_translations,name',
            'icon' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:10048',

        ]);

        if ($request->hasFile('icon')) {
            $data['icon'] = $this->uploadFile($request, 'icon', path: '/uploads/main_categories');
        }
        $mainCategory =   MainCategory::create([
            'ar' => ['name' => $request->name],
            'en' => ['name' => $request->name],
            'icon' => $data['icon'] ?? null
        ]);



        return response()->json(['message' => __('site.success'), 'mainCategory' => $mainCategory], 200);
    }

    public function update(Request $request, MainCategory $mainCategory)
    {
        $data = $request->validate([
            'name' => 'required|unique:main_category_translations,name,' . $mainCategory->id . ',main_category_id',
            'icon' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:10048',
        ]);

        if ($request->hasFile('icon')) {
            $data['icon'] = $this->uploadFile($request, 'icon', path: '/uploads/main_categories');
        }
        $mainCategory->update([
            'ar' => ['name' => $request->name],
            'en' => ['name' => $request->name],
            'icon' => $data['icon'] ?? $mainCategory->icon
        ]);
        return response()->json([
            'message' => __('site.success'),
            'mainCategory' => $mainCategory
        ], 200);
    }

    public function destroy(MainCategory $mainCategory)
    {
        $mainCategory->delete();
        return response()->json(['message' => __('site.success')], 200);
    }
}
