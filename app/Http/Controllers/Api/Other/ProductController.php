<?php

namespace App\Http\Controllers\Api\Other;

use App\Http\Controllers\Controller;
use App\Http\Requests\SearchProductsRequest;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{

    public function products(SearchProductsRequest $request)
    {
        $mainCategoryId = $request->input('main_category_id');
        $subCategoryId = $request->input('sub_category_id');
        $name = $request->input('name');
        $minPrice = $request->input('min_price');
        $maxPrice = $request->input('max_price');
        $sortOrder = $request->input('sort', 'newest');

        // Build the query using 'when' for conditional logic
        $products = Product::query()
            ->when($mainCategoryId, function ($query, $mainCategoryId) {
                return $query->where('main_category_id', $mainCategoryId);
            })
            ->when($subCategoryId, function ($query, $subCategoryId) {
                return $query->where('sub_category_id', $subCategoryId);
            })
            ->when($name, function ($query, $name) {
                return $query->whereTranslationLike('name', "%{$name}%");
            })
            ->when($minPrice, function ($query, $minPrice) {
                return $query->where('price', '>=', $minPrice);
            })
            ->when($maxPrice, function ($query, $maxPrice) {
                return $query->where('price', '<=', $maxPrice);
            })
            ->when($sortOrder, function ($query, $sortOrder) {
                if ($sortOrder === 'newest') {
                    return $query->orderBy('created_at', 'desc');
                } elseif ($sortOrder === 'oldest') {
                    return $query->orderBy('created_at', 'asc');
                }
            })
            ->paginate(20);
        return response()->json($products);
    }

    public function newProducts()
    {
        $products = Product::orderBy('created_at', 'desc')->take(4)->get();
        return response()->json($products);
    }
    public function popularProducts()
    {
        $products = Product::withCount('orders')->orderBy('orders_count', 'desc')->take(4)->get();
        return response()->json($products);
    }
    public function hotOfferProducts()
    {
        $products = Product::where('discount', '>', 0)->orderBy('discount', 'desc')->take(4)->get();
        return response()->json($products);
    }

    

}
