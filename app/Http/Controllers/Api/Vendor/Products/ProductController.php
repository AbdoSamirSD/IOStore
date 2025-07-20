<?php

namespace App\Http\Controllers\Api\Vendor\Products;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        //show all products for the vendor
        $vendor = $request->user();
        $products = $vendor->products()->with(['category', 'specifications.specification'])->paginate(20);
        return response()->json([
            'message' => 'Products retrieved successfully.',
            'data' => $products,
        ]);
    }

    public function show(Request $request, $id)
    {
        //show a single product by id
        $vendor = $request->user();
        $product = $vendor->products()->with(['category', 'specifications.specification', 'images'])->findOrFail($id);
        return response()->json([
            'message' => 'Product retrieved successfully.',
            'data' => $product,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'main_category_id' => 'required|exists:main_categories,id',
            'sub_category_id' => 'required|exists:sub_categories,id',
            'name' => 'required|string|max:255',
            'description' => 'required|string|max:1000',
            'details' => 'nullable|string|max:2000',
            'price' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0|max:100',
            'stock' => 'required|integer|min:0',
            'category_id' => 'required|exists:categories,id',
            'color' => 'required',
            'images' => 'array',
            'images.*' => 'image|mimes:jpeg,png,jpg|max:2048',
            'specifications' => 'array',
            'specifications.*.specification_id' => 'required|exists:specifications,id',
            'specifications.*.value' => 'required|string|max:255',

        ]);

        $vendor = $request->user();

        $product = $vendor->products()->create([
            'vendor_id' => $vendor->id,
            'main_category_id' => $request->main_category_id,
            'sub_category_id' => $request->sub_category_id,
            'price' => $request->price,
            'supplier_price' => $request->supplier_price ?? null,
            'discount' => $request->discount ?? 0,
            'stock' => $request->stock,
            'colors' => $request->color,
            'status' => 'pending',
            'is_active' => 'active', // Default to active
        ]);

        $product->translations()->create([
            'locale' => 'ar',
            'name' => $request->name,
            'description' => $request->description,
            'details' => $request->details ?? null,
        ]);

        if ($request->has('images')) {
            foreach ($request->images as $image) {
                $product->images()->create(['path' => $image->store('products', 'public')]);
            }
        }

        if ($request->has('specifications')) {
            foreach ($request->specifications as $spec) {
                $product->specifications()->create([
                    'specification_id' => $spec['specification_id'],
                    'value' => $spec['value'],
                ]);
            }
        }

        return response()->json([
            'message' => 'Product created successfully.',
            'data' => $product->load(['images', 'specifications.specification']),
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'main_category_id' => 'required|exists:main_categories,id',
            'sub_category_id' => 'required|exists:sub_categories,id',
            'name' => 'required|string|max:255',
            'description' => 'required|string|max:1000',
            'details' => 'nullable|string|max:2000',
            'price' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0|max:100',
            'stock' => 'required|integer|min:0',
            'color' => 'required',
            'images' => 'array',
            'images.*' => 'image|mimes:jpeg,png,jpg|max:2048',
            'specifications' => 'array',
            'specifications.*.specification_id' => 'required|exists:specifications,id',
            'specifications.*.value' => 'required|string|max:255',
        ]);

        $vendor = $request->user();
        $product = $vendor->products()->findOrFail($id);
        $product->update([
            'main_category_id' => $request->main_category_id,
            'sub_category_id' => $request->sub_category_id,
            'price' => $request->price,
            'supplier_price' => $request->supplier_price ?? null,
            'discount' => $request->discount ?? 0,
            'stock' => $request->stock,
            'colors' => $request->color,
        ]);

        if ($request->has('name') || $request->has('description') || $request->has('details')) {
            $product->translations()->where('locale', 'ar')->update([
                'name' => $request->name,
                'description' => $request->description,
                'details' => $request->details,
            ]);
        }

        if ($request->has('images')) {
            $product->images()->delete(); // Clear existing images
            foreach ($request->images as $image) {
                $product->images()->create(['path' => $image->store('products', 'public')]);
            }
        }

        if ($request->has('specifications')) {
            $product->specifications()->delete(); // Clear existing specifications
            foreach ($request->specifications as $spec) {
                $product->specifications()->create([
                    'specification_id' => $spec['specification_id'],
                    'value' => $spec['value'],
                ]);
            }
        }

        return response()->json([
            'message' => 'Product updated successfully.',
            'data' => $product->load(['images', 'specifications.specification']),
        ]);
    }

    public function destroy($id)
    {
        $vendor = auth()->user();
        $product = $vendor->products()->findOrFail($id);
        $product->images()->delete(); // Delete associated images
        $product->specifications()->delete(); // Delete associated specifications
        $product->translations()->delete(); // Delete translations
        $product->favorites()->delete(); // Delete associated favorites
        $product->cartItems()->delete(); // Delete associated cart items
        $product->orders()->delete(); // Delete associated orders
        $product->delete();

        return response()->json([
            'message' => 'Product deleted successfully.',
        ]);
    }

    public function toggle($id)
    {
        $vendor = auth()->user();
        $product = $vendor->products()->findOrFail($id);
        
        // Toggle the product status between 'active' and 'inactive'
        $product->is_active = $product->is_active === 'active' ? 'inactive' : 'active';
        $product->save();

        return response()->json([
            'message' => 'Product status updated successfully.',
            'data' => $product,
        ]);  
    }
}
