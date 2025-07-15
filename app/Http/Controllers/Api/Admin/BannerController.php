<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\Banner;
use App\Traits\FileUploadTrait;
use Illuminate\Http\Request;

class BannerController extends Controller
{
    use FileUploadTrait;
    public function index(Request $request)
    {
        $banners = Banner::latest()->get();
        return response()->json(['banners' => $banners], 200);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'product_id' => 'required|exists:products,id',
            'type' => 'required|in:offer,new_product',
        ]);

        if ($request->hasFile('image')) {
            $data['image_path'] = $this->uploadFile($request, 'image', path: '/uploads/banners');
        }
        $banner = Banner::create($data);
        return response()->json(['message' => __('site.success'), 'banner' => $banner], 200);
    }

    public function update(Request $request, Banner $banner)
    {
        $data = $request->validate([
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'product_id' => 'nullable|exists:products,id',
            'type' => 'nullable|in:offer,new_product',
        ]);

        if ($request->hasFile('image')) {
            $data['image_path'] = $this->uploadFile($request, 'image', path: '/uploads/banners');
        }
        $banner->update($data);
        return response()->json(['message' => __('site.success'), 'banner' => $banner], 200);
    }

    public function destroy(Banner $banner)
    {
        $banner->delete();
        return response()->json(['message' => __('site.success')], 200);
    }
}
