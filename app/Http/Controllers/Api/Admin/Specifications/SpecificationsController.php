<?php

namespace App\Http\Controllers\Api\Admin\Specifications;

use App\Http\Controllers\Controller;
use App\Models\MainCategory;
use App\Models\Specification;
use App\Models\SpecificationValue;
use App\Models\CategorySpecification;
use Illuminate\Http\Request;
use Validator;

class SpecificationsController extends Controller
{
    // manage specifications of categories
    public function index()
    {
        // code to list all specifications
        $admin = auth('admin')->user();
        if (!$admin) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $specifications = Specification::paginate(50);
        $count = $specifications->total();
        return response()->json([
            'message' => 'Specifications retrieved successfully.',
            'count' => $count,
            'specifications' => $specifications->getCollection()->transform(fn($spec) => [
                'specification_id' => $spec->id,
                'name' => $spec->name
            ]),
            'meta' => [
                'current_page' => $specifications->currentPage(),
                'last_page' => $specifications->lastPage(),
                'per_page' => $specifications->perPage(),
            ]
        ]);
    }

    // TODO: Implement methods for managing specifications
    
    // store or add new specifications to specifications table

    public function store(Request $request)
    {
        $admin = auth('admin')->user();
        if (!$admin) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $validator = Validator::make($request->all(), [
            'specifications' => 'required|array',
            'specifications.*.name' => 'required|string|max:255',
            'specifications.*.type' => 'required|string|max:255',
            'specifications.*.options' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $createdSpecs = [];

        foreach ($request->input('specifications') as $specData) {
            $exists = Specification::where('name', $specData['name'])->exists();
            if ($exists) {
                continue; 
            }

            $spec = Specification::create([
                'name' => $specData['name'],
                'type' => $specData['type'],
                'options' => $specData['options'] ?? null,
            ]);

            $createdSpecs[] = $spec;
        }

        if (empty($createdSpecs)) {
            return response()->json(['message' => 'No new specifications created. All Specifications already exist.'], 200);
        }
        $count = count($createdSpecs);
        return response()->json([
            'message' => 'Specifications created successfully.',
            'count' => $count,
            'specifications' => collect($createdSpecs)->map(fn($spec) => [
                'id' => $spec->id,
                'name' => $spec->name,
                'type' => $spec->type,
                'options' => $spec->options
            ])->toArray()
        ], 201);
    }
    
    public function update(Request $request, $id)
    {
        $admin = auth('admin')->user();
        if (!$admin) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $specification = Specification::find($id);
        if (!$specification) {
            return response()->json(['error' => 'Specification not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:specifications,name,'.$id,
            'type' => 'nullable|string|max:255',
            'options' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

         $exists = Specification::where('name', $request->name)
            ->where('id', '!=', $id)
            ->exists();

        if ($exists) {
            return response()->json(['error' => 'Specification name already exists.'], 409);
        }

        $specification->update($request->only('name', 'type', 'options'));


        return response()->json([
            'message' => 'Specification updated successfully.',
            'specification' => [
                'id' => $specification->id,
                'name' => $specification->name,
                'type' => $specification->type,
                'options' => $specification->options
            ]
        ]);
    }

    public function destroy($id)
    {
        $admin = auth('admin')->user();
        if (!$admin) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $specification = Specification::find($id);
        if (!$specification) {
            return response()->json(['error' => 'Specification not found'], 404);
        }
        // if the specification is in use, return an error
        if ($specification->products()->exists()) {
            return response()->json(['error' => 'Specification is in use and cannot be deleted.'], 409);
        }
        $specification->delete();

        return response()->json(['message' => 'Specification deleted successfully.']);
    }


    public function indexValues($specificationId)
    {
        $admin = auth()->guard('admin')->user();
        if (!$admin) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $specification = Specification::with('values')->find($specificationId);
        if (!$specification) {
            return response()->json(['error' => 'Specification not found'], 404);
        }

        $count = $specification->values->count();
        return response()->json([
            'message' => 'Specification values retrieved successfully.',
            'count' => $count,
            'values' => [
                'id' => $specification->id,
                'name' => $specification->name,
                'values' => $specification->values->map(fn($value) => [
                    'id' => $value->id,
                    'value' => $value->value,
                ])->toArray()
            ]
        ]);
    }

    public function storeValues ($specificationId, Request $request){
        $admin = auth('admin')->user();
        if (!$admin) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $specification = Specification::find($specificationId);
        if (!$specification) {
            return response()->json(['error' => 'Specification not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'values' => 'required|array',
            'values.*.value' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        $addedValues = [];

        foreach ($request->input('values') as $valueData) {
            $exists = SpecificationValue::where('specification_id', $specificationId)
                ->where('value', $valueData['value'])
                ->exists();
            if ($exists) {
                continue;
            }
            $value[] = $specification->values()->create([
                'specification_id' => $specificationId,
                'value' => $valueData['value'],
            ]);

            $addedValues[] = $value;
        }

        $count = count($specification->values);
        return response()->json([
            'message' => 'Specification values created successfully.',
            'count' => $count,
            'values' => $specification->values->map(fn($value) => [
                'id' => $value->id,
                'value' => $value->value,
            ])->toArray()
        ], 201);
    }

    public function updateValues(Request $request, $specificationId)
    {
        $admin = auth('admin')->user();
        if (!$admin) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $specification = Specification::find($specificationId);
        if (!$specification) {
            return response()->json(['error' => 'Specification not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'values' => 'required|array',
            'values.*.id' => 'required|exists:specification_values,id',
            'values.*.value' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $updatedValues = [];

        foreach ($request->input('values') as $valueData) {
            $value = SpecificationValue::where('specification_id', $specificationId)
                ->where('id', $valueData['id'])
                ->first();
            if ($value) {
                $exists = SpecificationValue::where('specification_id', $specificationId)
                    ->where('value', $valueData['value'])
                    ->where('id', '!=', $valueData['id'])
                    ->exists();
                if ($exists) {
                   continue;
                }
                $value->update(['value' => $valueData['value']]);
                $updatedValues[] = $value;
            }
        }

        $count = $specification->values->count();
        return response()->json([
            'message' => 'Specification value updated successfully.',
            'count' => $count,
            'values' => $specification->values->map(fn($value) => [
                'id' => $value->id,
                'value' => $value->value,
            ])->toArray()
        ]);
    }

    public function destroyValues($valueId)
    {
        $admin = auth('admin')->user();
        if (!$admin) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $value = SpecificationValue::find($valueId);
        if (!$value) {
            return response()->json(['error' => 'Value not found'], 404);
        }

        $value->delete();

        return response()->json(['message' => 'Specification value deleted successfully.']);
    }

    public function destroyAllValues($specificationId)
    {
        $admin = auth('admin')->user();
        if (!$admin) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $specification = Specification::find($specificationId);
        if (!$specification) {
            return response()->json(['error' => 'Specification not found'], 404);
        }

        if (! $specification->values()->exists()) {
            return response()->json(['message' => 'No specification values found to delete.'], 404);
        }
        $specification->values()->delete();

        return response()->json(['message' => 'All specification values deleted successfully.']);
    }

    public function categorySpecifications($categoryId)
    {
        $admin = auth()->guard('admin')->user();
        if (!$admin) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    
        $mainCategory = MainCategory::with('specifications.values')->find($categoryId);
        if (!$mainCategory) {
            return response()->json(['error' => 'Main category not found'], 404);
        }
    
        $specifications = $mainCategory->specifications()->with('values')->paginate(50);
        $specifications->getCollection()->transform(fn($spec) => [
            'id' => $spec->id,
            'name' => $spec->name,
            'options' => $spec->options,
            'values' => $spec->values->map(fn($value) => [
                'id' => $value->id,
                'value' => $value->value,
            ])->toArray()
        ]);
        if($specifications->isEmpty()) {
            return response()->json(['message' => 'No specifications found for this category. Please add some.'], 200);
        }

        $count = $specifications->total();
        return response()->json(
            [
                'message' => 'Specifications retrieved successfully.',
                'count' => $count,
                'specifications' => $specifications
            ]
        );
    }

    // link category to its specifications
    public function addCategorySpecifications($categoryId, Request $request)
    {
        $admin = auth()->guard('admin')->user();
        if (!$admin) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        if (!MainCategory::where('id', $categoryId)->exists()) {
            return response()->json(['error' => 'Main category not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'specifications' => 'required|array',
            'specifications.*.id' => 'required|exists:specifications,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        $existingSpecs = CategorySpecification::where('category_id', $categoryId)
                ->pluck('specification_id')
                ->toArray();

        $newSpecs = collect($request->input('specifications'))
            ->pluck('id')
            ->reject(fn($id) => in_array($id, $existingSpecs))
            ->map(fn($id) => [
                'category_id' => $categoryId,
                'specification_id' => $id,
                'created_at' => now(),
                'updated_at' => now(),
            ])
            ->toArray();
        
        if (!empty($newSpecs)) {
            CategorySpecification::insert($newSpecs);
        }
        $categorySpecifications = CategorySpecification::where('category_id', $categoryId)
            ->with('specification')
            ->get();

        $count = $categorySpecifications->count();
        return response()->json([
            'message' => 'Category specifications added successfully.',
            'count' => $count,
            'category_specifications' => $categorySpecifications->map(fn($cs) => [
                'id' => $cs->id,
                'specification_id' => $cs->specification_id,
                'name' => $cs->specification->name,
            ])->toArray()
        ]);
    }

    public function updateCategorySpecifications($categoryId, Request $request)
    {
        $admin = auth()->guard('admin')->user();
        if (!$admin) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        if (!MainCategory::where('id', $categoryId)->exists()) {
            return response()->json(['error' => 'Main category not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'specifications' => 'required|array',
            'specifications.*.id' => 'required|exists:specifications,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $existingSpecs = CategorySpecification::where('category_id', $categoryId)
            ->pluck('specification_id')
            ->toArray();

        $newSpecs = collect($request->input('specifications'))
            ->pluck('id')
            ->reject(fn($id) => in_array($id, $existingSpecs))
            ->map(fn($id) => [
                'category_id' => $categoryId,
                'specification_id' => $id,
                'created_at' => now(),
                'updated_at' => now(),
            ])
            ->toArray();

        if (!empty($newSpecs)) {
            CategorySpecification::insert($newSpecs);
        }
        $categorySpecifications = CategorySpecification::where('category_id', $categoryId)
            ->with('specification')
            ->get();

        $count = $categorySpecifications->count();
        return response()->json([
            'message' => 'Category specifications updated successfully.',
            'count' => $count,
            'category_specifications' => $categorySpecifications->map(fn($cs) => [
                'id' => $cs->id,
                'specification_id' => $cs->specification_id,
                'name' => $cs->specification->name,
            ])->toArray()
        ]);
    }

    public function destroyCategorySpecifications($categoryId, $specificationId)
    {
        $admin = auth()->guard('admin')->user();
        if (!$admin) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        if (!MainCategory::where('id', $categoryId)->exists()) {
            return response()->json(['error' => 'Main category not found'], 404);
        }

        $categorySpecification = CategorySpecification::where('category_id', $categoryId)
            ->where('specification_id', $specificationId)
            ->first();

        if (!$categorySpecification) {
            return response()->json(['error' => 'Category specification not found'], 404);
        }

        $categorySpecification->delete();

        $remainingSpecifications = CategorySpecification::where('category_id', $categoryId)
            ->with('specification')
            ->get();

        return response()->json([
            'message' => 'Category specification deleted successfully.',
            'count' => $remainingSpecifications->count(),
            'category_specifications' => $remainingSpecifications->map(fn($cs) => [
                'id' => $cs->id,
                'specification_id' => $cs->specification_id,
                'name' => $cs->specification->name,
            ])->toArray()
        ]);
    }

    public function destroyAllCategorySpecifications($categoryId)
    {
        $admin = auth()->guard('admin')->user();
        if (!$admin) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        if (!MainCategory::where('id', $categoryId)->exists()) {
            return response()->json(['error' => 'Main category not found'], 404);
        }

        CategorySpecification::where('category_id', $categoryId)->delete();

        return response()->json([
            'message' => 'All category specifications deleted successfully.',
        ]);
    }
}
