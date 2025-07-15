<?php

namespace App\Http\Controllers\Api\Admin\Products;

use App\Http\Controllers\Controller;
use App\Http\Requests\AddProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\MainCategory;
use App\Models\Product;
use App\Models\SubCategory;
use Illuminate\Http\Request;
use App\Traits\HandlesImages;

class ProductsController extends Controller
{
    use HandlesImages;

    public function index(Request $request)
    {
        $products = Product::whereTranslationLike('name', '%' . $request->name . '%')->latest()->paginate(perPage: 50);
        return response()->json(['products' => $products], 200);
    }
    // add new product
    public function store(AddProductRequest $request)
    {
        $productData = $request->validated();

        $product = Product::create($productData);
        // Add images if provided
        if (isset($productData['images'])) {
            $this->uploadImages($request, $product, 'images', '/uploads/products');
        }
        return $product;
    }

    public function update(UpdateProductRequest $request, Product $product)
    {
        $productData = $request->validated();

        $product->update($productData);


        // Save images if provided
        if (isset($productData['images'])) {
            $this->uploadImages($request, $product, 'images', '/uploads/products');
        }

        // Delete images if requested
        if (isset($productData['delete_images'])) {
            // $product->images()->whereIn('id', $productData['delete_images'])->delete();
            $this->deleteImages($productData['delete_images'], $product);
        }

        return response()->json(['message' => __('site.success'), 'product' => $product], 200);
    }

    public function destroy(Product $product)
    {
        \DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        $product->delete();
        \DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        return response()->json(['message' => __('site.success')], 200);
    }

    public function import(Request $request)
    {
        // Validate the uploaded file to ensure it's a CSV
        $request->validate([
            'file' => 'required|mimes:csv',
        ]);

        $file = $request->file('file');
        $filePath = $file->getRealPath();

        // Open the CSV file and read its contents
        if (($handle = fopen($filePath, 'r')) !== false) {
            // Skip the header row
            fgetcsv($handle);

            while (($row = fgetcsv($handle, 10000, ',')) !== false) {
                // Adjust indexes based on your CSV file's structure
                $subcategory = SubCategory::whereTranslationLike('name', '%' . $row[6] . '%')->first();

                $productPrice = floatval(str_replace([' EGP', 'EGP', ' '], '', $row[1])); // Product Price
                $productPriceToSell = floatval(str_replace([' EGP', 'EGP', ' '], '', $row[2])); // Price to Sell
                Product::create([
                    'main_category_id' => $subcategory->main_category_id,
                    'sub_category_id' => $subcategory->id,
                    'price' => (float) $productPriceToSell, // Product Price to Sell
                    'supplier_price' => $productPrice, // Product Price
                    'stock' => 0, // Assuming 0 as stock value from the file is missing
                    'discount' => (float) $row[7], // Discount
                    'ar' => [
                        'name' => $row[0], // Product Name
                        'description' => $row[3], // Description
                    ],
                ]);
            }
            fclose($handle);
        }

        return response()->json(['message' => 'Products Imported Successfully.']);
    }
}
