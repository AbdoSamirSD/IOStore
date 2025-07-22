<?php

namespace App\Http\Controllers\Api\Vendor\Products;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MainCategory;
use App\Models\Product;
use App\Models\Specification;
use App\Models\ImageItem;
use Validator;
use App\Models\SpecificationValue;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $vendor = $request->user();
        $products = $vendor->products()
            ->with(['translations', 'images',])
            ->get()
            ->map(function ($product) {return[
                'id' => $product->id,
                'name' => $product->name,
                'description' => $product->description,
                'price' => $product->price,
                'discount' => $product->discount,
                'stock' => $product->stock,
                'images' => $product->images->map(function ($image) {
                    return asset('storage/' . $image->path);
                }),
                'is_active' => $product->is_active,
            ];
        });

        if ($products->isEmpty()) {
            return response()->json([
                'message' => 'No products found.',
                'data' => [],
            ], 404);
        }

        return response()->json([
            'message' => 'Products retrieved successfully.',
            'data' => $products,
        ]);
    }

    public function show(Request $request, $id)
    {
        //show a single product by id
        $vendor = $request->user();
        $product = $vendor->products()
            ->with(['translations', 'images', 'mainCategory', 'specificationsValues.specification'])
            ->find($id);

        if (!$product) {
            return response()->json([
                'message' => 'Product not found.',
            ], 404);
        }

        return response()->json([
            'message' => 'Product retrieved successfully.',
            'data' => [
                'id' => $product->id,
                'name' => $product->name,
                'description' => $product->description,
                'price' => $product->price,
                'discount' => $product->discount,
                'stock' => $product->stock,
                'status' => $product->status,
                'is_active' => $product->is_active,
                'main_category' => $product->mainCategory ? $product->mainCategory->name : null,
                'images' => $product->images->map(function ($image) {
                    return asset('storage/' . $image->path);
                }),
                'specifications' => $product->specificationsValues->map(function ($spec) {
                    return [
                        'specification' => $spec->specification->name,
                        'value' => $spec->value,
                    ];
                }),
            ]
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'main_category_id' => 'required|exists:main_categories,id',
            'sub_category_id' => 'required|exists:sub_categories,id',
            'name' => 'required|string|max:255',
            'description' => 'required|string|max:1000',
            'price' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'stock' => 'nullable|integer|min:0',
            'images' => 'array',
            'images.*' => 'image|mimes:jpeg,png,jpg|max:2048',
            'specifications' => 'array',
            'specifications.*.name' => 'string|exists:specifications,name',
            'specifications.*.value' => 'string|max:255|exists:specification_values,value',
        ]);

        if ($validator->fails()){
            return response()->json(
                [
                    'message' => 'Validator Error',
                    'errors' => $validator -> errors(),
                ], 422
            );
        }

        \Log::info('✅ Step 1: Validation passed');

        $vendor = $request->user();
        \Log::info('✅ Step 2: Got vendor', ['vendor_id' => $vendor->id]);

        $product = $vendor->products()->create([
            'main_category_id' => $request->main_category_id,
            'sub_category_id' => $request->sub_category_id,
            'price' => $request->price,
            'supplier_price' => $request->supplier_price ?? null,
            'discount' => $request->discount ?? 0,
            'stock' => $request->stock,
        ]);
        \Log::info('✅ Step 3: Product created', ['product_id' => $product->id]);


        // Create translations
        $product->translations()->create([
            'locale' => 'en', 
            'name' => $request->name,
            'description' => $request->description,
        ]);
        \Log::info('✅ Step 4: Product inserted successfully', ['product_id' => $product->id]);


        // Handle images
        if ($request->has('images')) {
            foreach ($request->images as $image) {
                $product->images()->create(['image_path' => $image->store('products', 'public')]);
            }
        }
        \Log::info('✅ Step 5: Images processed', ['product_id' => $product->id]);

        // Handle specifications
        foreach($request->specifications as $spec){

            $specification = Specification::where('name', $spec['name'])->first();

            if ($specification) {
                $product->specificationsValues()->create([
                    'specification_id' => $specification->id,
                    'value' => $spec['value'],
                ]);
            }else {
                return response()->json([
                    'message' => 'Invalid specification or value.',
                ], 422);
            }
        }
        \Log::info('✅ Step 6: Specifications processed', ['product_id' => $product->id]);

        return response()->json([
            'message' => 'Product created successfully.',
            'data' => $product->load(['translations', 'images', 'specificationsValues.specification']),
        ]);
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
