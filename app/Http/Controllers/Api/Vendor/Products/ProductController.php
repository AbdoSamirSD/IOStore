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
use App\Models\Order;

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

        $vendor = $request->user();
        $product = $vendor->products()->create([
            'main_category_id' => $request->main_category_id,
            'price' => $request->price,
            'supplier_price' => $request->supplier_price ?? null,
            'discount' => $request->discount ?? 0,
            'stock' => $request->stock,
        ]);

        // Create translations
        $product->translations()->create([
            'locale' => 'en', 
            'name' => $request->name,
            'description' => $request->description,
        ]);

        // Handle images
        if ($request->has('images')) {
            foreach ($request->images as $image) {
                $product->images()->create(['image_path' => $image->store('products', 'public')]);
            }
        }

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

        return response()->json([
            'message' => 'Product created successfully.',
            'data' => $product->load(['translations', 'images', 'specificationsValues.specification']),
        ]);
    }

    public function update(Request $request, $id){
        $validator = Validator::make($request->all(), [
            'main_category_id' => 'required|exists:main_categories,id',
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

        $vendor = $request->user();
        $product = $vendor->products()->findOrFail($id);

        // Update product details
        $product->update([
            'main_category_id' => $request->main_category_id,
            'price' => $request->price,
            'supplier_price' => $request->supplier_price ?? null,
            'discount' => $request->discount ?? 0,
            'stock' => $request->stock,
        ]);

        // Update translations
        $product->translations()->updateOrCreate(
            ['locale' => 'en'], 
            [
                'name' => $request->name,
                'description' => $request->description,
            ]
        );

        // Handle images
        if ($request->has('images')) {
            $product->images()->delete();
            foreach ($request->images as $image) {
                $product->images()->create(['image_path' => $image->store('products', 'public')]);
            }
        }

        // Handle specifications
        if ($request->has('specifications')) {
            foreach($request->specifications as $spec){
                $specification = Specification::where('name', $spec['name'])->first();

                if ($specification) {
                    $product->specificationsValues()->updateOrCreate(
                        ['specification_id' => $specification->id],
                        ['value' => $spec['value']]
                    );
                } else {
                    return response()->json([
                        'message' => 'Invalid specification or value.',
                    ], 422);
                }
            }
        }
        

        return response()->json([
            'message' => 'Product updated successfully.',
            'data' => $product->load(['translations', 'images', 'specificationsValues.specification']),
        ]);
    }

    public function destroy($id)
    {
        $vendor = auth()->user();
        $product = $vendor->products()->findOrFail($id);

        $isInActiveOrder = Order::whereHas('items', function ($query) use ($product) {
            $query->where('product_id', $product->id);
        })->whereNotIn('status', ['pending', 'accepted', 'preparing', 'on_the_way', 'delivered'])->exists();

        if ($isInActiveOrder) {
            return response()->json([
                'message' => 'Cannot delete product with active orders.',
            ], 422);
        }

        $product->images()->delete();
        $product->specificationsValues()->delete();
        $product->translations()->delete();
        $product->favorites()->delete();
        $product->cartItems()->delete();
        $product->delete();

        return response()->json([
            'message' => 'Product deleted successfully.',
        ]);
    }

    public function toggle($id)
    {
        $vendor = auth()->user();
        $product = $vendor->products()->find($id);

        if(!$product){
            return response()->json([
                'message' => 'Product not found.',
            ], 404);
        }
        
        // Toggle the product status between 'active' and 'inactive'
        $product->is_active = $product->is_active === 'active' ? 'inactive' : 'active';
        $product->save();

        return response()->json([
            'message' => 'Product status updated successfully.',
            'data' => $product->is_active,
        ]);  
    }
}
