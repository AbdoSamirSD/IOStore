<?php

namespace App\Http\Controllers\Api\Other;

use App\Http\Controllers\Controller;
use App\Http\Requests\SearchProductsRequest;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{

    
    // public function products(SearchProductsRequest $request)
    // {
    //     $mainCategoryId = $request->input('main_category_id');
    //     $subCategoryId = $request->input('sub_category_id');
    //     $name = $request->input('name');
    //     $minPrice = $request->input('min_price');
    //     $maxPrice = $request->input('max_price');
    //     $sortOrder = $request->input('sort', 'newest');

    //     // Build the query using 'when' for conditional logic
    //     $products = Product::query()
    //         ->when($mainCategoryId, function ($query, $mainCategoryId) {
    //             return $query->where('main_category_id', $mainCategoryId);
    //         })->when($subCategoryId, function ($query, $subCategoryId) {
    //             return $query->where('sub_category_id', $subCategoryId);
    //         })
    //         ->when($name, function ($query, $name) {
    //             return $query->whereTranslationLike('name', "%{$name}%");
    //         })
    //         ->when($minPrice, function ($query, $minPrice) {
    //             return $query->where('price', '>=', $minPrice);
    //         })
    //         ->when($maxPrice, function ($query, $maxPrice) {
    //             return $query->where('price', '<=', $maxPrice);
    //         })
    //         ->when($sortOrder, function ($query, $sortOrder) {
    //             if ($sortOrder === 'newest') {
    //                 return $query->orderBy('created_at', 'desc');
    //             } elseif ($sortOrder === 'oldest') {
    //                 return $query->orderBy('created_at', 'asc');
    //             }
    //         })
    //         ->paginate(20);
    //     return response()->json($products);
    // }

    // public function newProducts()
    // {
    //     $products = Product::orderBy('created_at', 'desc')->take(4)->get();
    //     return response()->json($products);
    // }
    // public function popularProducts()
    // {
    //     $products = Product::withCount('orders')->orderBy('orders_count', 'desc')->take(4)->get();
    //     return response()->json($products);
    // }
    // public function hotOfferProducts()
    // {
    //     $products = Product::where('discount', '>', 0)->orderBy('discount', 'desc')->take(4)->get();
    //     return response()->json($products);
    // }

    public function products(Request $request)
    {
        $locale = 'en';
        $perPage = $request->get('per_page', 10); // يمكن تحديد عدد النتائج لكل صفحة

        $productsQuery = Product::with(['images', 'translations'])
            ->where('is_active', 'active')
            ->where('status', 'approved')
            ->orderBy('created_at', 'desc');

        $products = $productsQuery->paginate($perPage);

        if ($products->isEmpty()) {
            return response()->json(['message' => 'No products found'], 404);
        }

        $filteredProducts = $products->getCollection()->map(function ($product) use ($locale) {
            $translation = $product->translations->where('locale', $locale)->first();
            return [
                'id' => $product->id,
                'name' => $translation ? $translation->name : $product->name,
                'description' => $translation ? $translation->description : $product->description,
                'price' => $product->price,
                'discount' => $product->discount,
                'stock' => $product->stock,
                'images' => $product->images->map(function ($image) {
                    return asset('storage/' . $image->image_path);
                }),
            ];
        });

        $products->setCollection($filteredProducts);

        return response()->json([
            'message' => 'products retrieved successfully',
            'products' => $products
        ], 200);
    }

    public function show($id){
        //show all details for specific product
        $product = Product::find($id);
        if(!$product){
            return response()->json([
                'message' => 'product not found'
            ], 404);
        }

        $productDetails = $product->with([
            'images',
            'translations',
            'specificationsValues.specification'
        ]);

        return response()->json([
            'message' => 'product retrieved successfully',
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'description' => $product->description,
                'details' => $product->details,
                'instructions' => $product->instructions,
                'price' => $product->price,
                'discount' => $product->discount,
                'stock' => $product->stock,
                'main_category' => $product->mainCategory ? $product->mainCategory->name : null,
                'images' => $product->images->map(function ($image) {
                    return asset('storage/' . $image->image_path);
                }),
                'specifications' => $product->specificationsValues->map(function ($spec) {
                    return [
                        'specification' => $spec->specification->name,
                        'value' => $spec->value,
                    ];
                }),
            ]
        ], 200);
    }

    public function reviews($id){
        $product = Product::find($id);
        if(!$product){
            return response()->json([
                'message' => 'product not found'
            ], 404);
        }

        $reviews = $product->reviews()->with(['user'])->get();
        if($reviews->isEmpty()){
            return response()->json([
                'message' => 'No reviews found for this product'
            ], 404);
        }

        return response()->json([
            'message' => 'Reviews retrieved successfully',
            'reviews' => $reviews->map(function ($review) {
                return [
                    'id' => $review->id,
                    'rating' => $review->rating,
                    
                    'comment' => $review->comment,
                    'user' => [
                        'id' => $review->user->id,
                        'name' => $review->user->name,
                    ],
                    'created_at' => $review->created_at,
                ];
            }),
            'average_rating' => round($product->reviews->avg('rating'), 1),
            'rating_count' => $product->reviews()->count(),
        ], 200);
    }

    public function relatedProducts($id){
        $product = Product::find($id);
        if(!$product){
            return response()->json([
                'message' => 'product not found'
            ], 404);
        }

        $relatedProducts = Product::where('main_category_id', $product->main_category_id)
            ->where('id', '!=', $id)
            ->where('is_active', 'active')
            ->where('status', 'approved')
            ->with(['images', 'translations'])
            ->take(4)
            ->get();

        if($relatedProducts->isEmpty()){
            return response()->json([
                'message' => 'No related products found'
            ], 404);
        }

        return response()->json([
            'message' => 'Related products retrieved successfully',
            'related_products' => $relatedProducts->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'price' => $product->price,
                    'discount' => $product->discount,
                    'images' => $product->images->map(function ($image) {
                        return asset('storage/' . $image->image_path);
                    }),

                ];
            })
        ], 200);
    }

    public function newProducts(){
        $products = Product::with(['images', 'translations'])
            ->where('is_active', 'active')
            ->where('status', 'approved')
            ->orderBy('created_at', 'desc')
            ->take(4)
            ->get();

        if($products->isEmpty()){
            return response()->json(['message' => 'No new products found'], 404);
        }

        return response()->json([
            'message' => 'New products retrieved successfully',
            'products' => $products->map(function ($product){
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'description' => $product->description,
                    'price' => $product->price,
                    'discount' => $product->discount,
                    'images' => $product->images->map(function ($image) {
                        return asset('storage/' . $image->image_path);
                    }),
                ];
            })
        ], 200);
    }

    public function popularProducts(){
        $products = Product::withCount('orders')
            ->where('is_active', 'active')
            ->where('status', 'approved')
            ->orderBy('orders_count', 'desc')
            ->take(4)
            ->get();

        if($products->isEmpty()){
            return response()->json(['message' => 'No popular products found'], 404);
        }

        return response()->json([
            'message' => 'Popular products retrieved successfully',
            'products' => $products->map(function ($product){
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'description' => $product->description,
                    'price' => $product->price,
                    'discount' => $product->discount,
                    'images' => $product->images->map(function ($image) {
                        return asset('storage/' . $image->image_path);
                    }),
                ];
            })
        ], 200);
    }

    public function hotOfferProducts(){
        $products = Product::where('discount', '>', 0)
            ->where('is_active', 'active')
            ->where('status', 'approved')
            ->orderBy('discount', 'desc')
            ->take(4)
            ->get();

        if($products->isEmpty()){
            return response()->json(['message' => 'No hot offer products found'], 404);
        }

        return response()->json([
            'message' => 'Hot offer products retrieved successfully',
            'products' => $products->map(function ($product){
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'description' => $product->description,
                    'price' => $product->price,
                    'discount' => $product->discount,
                    'images' => $product->images->map(function ($image) {
                        return asset('storage/' . $image->image_path);
                    }),
                ];
            })
        ], 200);
    }

    public function searchProducts(Request $request)
    {
        $query = $request->input('query');
        if (!$query) {
            return response()->json(['message' => 'Query parameter is required'], 400);
        }

        $products = Product::with(['images', 'translations', 'mainCategory', 'specificationsValues'])
            ->where('is_active', 'active')
            ->where('status', 'approved')
            ->where(function ($q) use ($query) {
                $q->whereTranslationLike('name', "%{$query}%")
                  ->orWhereTranslationLike('description', "%{$query}%")
                ->orWhereHas('mainCategory', function ($q) use ($query) {
                    $q->whereTranslationLike('name', "%{$query}%");
                })
                ->orWhereHas('specificationsValues', function ($q) use ($query) {
                    $q->where('value', 'like', "%{$query}%")
                    ->orwhereHas('specification', function ($q) use ($query) {
                        $q->where('name', 'like', "%{$query}%");
                    });
                })
                ->orWhere('price', 'like', "%{$query}%")
                ->orWhere('discount', 'like', "%{$query}%")
                ->orWhere('id', 'like', "%{$query}%");
            });

        if ($products->isEmpty()) {
            return response()->json(['message' => 'No products found'], 404);
        }

        return response()->json([
            'message' => 'Search results retrieved successfully',
            'products' => $products->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'description' => $product->description,
                    'price' => $product->price,
                    'discount' => $product->discount,
                    'images' => $product->images->map(function ($image) {
                        return asset('storage/' . $image->image_path);
                    }),
                ];
            })
        ], 200);
    }
}
