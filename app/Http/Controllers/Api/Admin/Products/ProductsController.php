<?php

namespace App\Http\Controllers\Api\Admin\Products;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Traits\HandlesImages;
use Validator;

class ProductsController extends Controller
{
    use HandlesImages;

    public function index()
    {
        $admin = auth('admin')->user();
        if (!$admin) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        $products = Product::with(['mainCategory', 'vendor'])
            ->where('status', ['approved', 'pending'])
            ->latest()->paginate(50);
        $count = $products->total();
        $products->getCollection()->transform(function ($product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'main_category' => $product->mainCategory->name,
                'price' => $product->price,
                'status' => $product->status,
                'is_active' => $product->is_active,
                'vendor' => $product->vendor->full_name
            ];
        });
        return response()->json([
            'message' => 'Products retrieved successfully',
            'total' => $count,
            'data' => $products
        ]);
    }
    public function search(Request $request)
    {
        // Implement search logic here
        $admin = auth('admin')->user();
        if (!$admin) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $validator = Validator::make($request->all(), [
            'query' => 'required|string|max:255',
        ]);
        if ($validator->fails()) {
            return response()->json(['message' => 'Invalid search query'], 422);
        }

        // Perform the search
        $search = $request->input('query');
        $products = Product::with(['translations', 'mainCategory.translations', 'vendor'])
            ->Where(function($q) use ($search){
                $q->whereHas('translations', function($query) use ($search) {
                    $query->where('name', 'like', '%' . $search . '%')
                          ->orWhere('description', 'like', '%' . $search . '%')
                          ->orWhere('details', 'like', '%' . $search . '%')
                          ->orWhere('instructions', 'like', '%' . $search . '%');
                })
                ->orWhereHas('mainCategory.translations', function($query) use ($search) {
                    $query->where('name', 'like', '%' . $search . '%');
                });
            })
            ->orWhere('price', 'like', '%' . $search . '%')
            ->latest()
            ->paginate(50);

        $count = $products->total();

        if ($count === 0) {
            return response()->json([
                'message' => 'No products found',
            ]);
        }

        $products->getCollection()->transform(function ($product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'main_category' => $product->mainCategory->name,
                'price' => $product->price,
                'status' => $product->status,
                'is_active' => $product->is_active,
                'vendor' => $product->vendor->full_name
            ];
        });

        return response()->json([
            'message' => 'Search results retrieved successfully',
            'total' => $count,
            'data' => $products
        ]);
    }

    public function pendingProducts()
    {
        $admin = auth('admin')->user();
        if (!$admin) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $products = Product::with(['mainCategory', 'vendor'])->where('status', 'pending')->latest()->paginate(50);
        $count = $products->total();

        if ($count === 0) {
            return response()->json([
                'message' => 'No pending products found',
            ]);
        }

        $products->getCollection()->transform(function ($product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'main_category' => $product->mainCategory->name,
                'price' => $product->price,
                'status' => $product->status,
                'is_active' => $product->is_active,
                'vendor' => $product->vendor->full_name
            ];
        });

        return response()->json([
            'message' => 'pending products retrieved successfully',
            'total' => $count,
            'data' => $products
        ]);
    }

    public function updateStatus(Request $request, $id)
    {
        $admin = auth('admin')->user();
        if (!$admin) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $validator = Validator::make($request->all(), [
            'status' => 'required|string|in:approved,rejected',
        ]);
        if ($validator->fails()) {
            return response()->json(['message' => 'Invalid status'], 422);
        }

        $product = Product::find($id);
        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        $product->update([
            'status' => $request->input('status'),
        ]);

        return response()->json([
            'message' => 'Product status updated successfully',
            'data' => [
                'id' => $product->id,
                'name' => $product->name,
                'status' => $product->status
            ]
        ]);
    }

    public function destroy($id)
    {
        $admin = auth('admin')->user();
        if (!$admin) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $product = Product::find($id);
        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        if ($product->orderItems()->exists()) {
            $product->is_active = 'inactive';
            $product->save();
            return response()->json(['message' => 'Product cannot be deleted because it has associated orders. It is inactive right now.'], 422);
        }

        $product->delete();

        return response()->json([
            'message' => 'Product deleted successfully',
        ]);
    }
}
