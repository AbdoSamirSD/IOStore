<?php

namespace App\Http\Controllers\Api\Admin\Vendor;

use App\Http\Controllers\Controller;
use App\Models\CommissionPlan;
use App\Models\CommissionRange;
use Illuminate\Http\Request;
use App\Models\Vendor;
use Validator;
use DB;

class VendorController extends Controller
{
    public function index()
    {
        $admin = auth('admin')->user();
        if (!$admin) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Fetch vendors from the database
        $vendors = Vendor::paginate(20);
        $vendors->getCollection()->transform(function ($vendor) {
            return [
                'id' => $vendor->id,
                'name' => $vendor->full_name,
                'store_name' => $vendor->store_name,
                'profile_image' => $vendor->profile_image,
            ];
        });
        $count = $vendors->total();
        return response()->json(
            [
                'count' => $count,
                'vendors' => $vendors->items(),
                'pagination' => [
                    'total' => $count,
                    'current_page' => $vendors->currentPage(),
                    'last_page' => $vendors->lastPage(),
                    'per_page' => $vendors->perPage(),
                ]
            ]);
    }

    public function show($id)
    {
        $admin = auth('admin')->user();
        if (!$admin) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $vendor = Vendor::with(['products', 'commissionPlans.ranges'])->find($id);
        if (!$vendor) {
            return response()->json(['error' => 'Vendor not found'], 404);
        }

        
        return response()->json(
            [
                'message' => 'Vendor retrieved successfully',
                'data' => [
                    'id' => $vendor->id,
                    'name' => $vendor->full_name,
                    'store_name' => $vendor->store_name,
                    'email' => $vendor->email,
                    'phone' => $vendor->phone,
                    'address' => $vendor->address,
                    'expected_delivery_time' => $vendor->expected_delivery_time,
                    'profile_image' => $vendor->profile_image,
                    'commercial_register' => $vendor->commercial_register,
                    'commission_plans' => $vendor->commissionPlans->map(function ($plan) {
                        return [
                            'id' => $plan->id,
                            'plan_name' => $plan->plan_name,
                            'commission_type' => $plan->commission_type,
                            'is_active' => $plan->is_active,
                            'fixed_percentage' => $plan->fixed_percentage,
                            'ranges' => $plan->ranges->map(function ($range) {
                                return [
                                    'id' => $range->id,
                                    'min' => $range->min_value,
                                    'max' => $range->max_value,
                                    'percentage' => $range->percentage,
                                ];
                            }),
                        ];
                    }),
                    'products' => $vendor->products->map(function ($product) {
                        return [
                            'id' => $product->id,
                            'name' => $product->name,
                            'price' => $product->price,
                            'description' => $product->description,
                            'status' => $product->status,
                            'is_active' => $product->is_active,
                        ];
                    }),
                ]
            ]
        );
    }

    public function pendingVendors()
    {
        //
        $admin = auth('admin')->user();
        if (!$admin) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $pendingVendors = Vendor::where('is_active', 'pending')->get();
        return response()->json(
            [
                'count' => $pendingVendors->count(),
                'vendors' => $pendingVendors->map(function ($vendor) {
                    return [
                        'id' => $vendor->id,
                        'name' => $vendor->full_name,
                        'store_name' => $vendor->store_name,
                        'profile_image' => $vendor->profile_image,
                    ];
                }),
            ]
        );
    }

    public function updateStatus($id, Request $request)
    {
        $admin = auth('admin')->user();
        if (!$admin) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $vendor = Vendor::find($id);
        if (!$vendor) {
            return response()->json(['error' => 'Vendor not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'status' => 'required|string|in:active,inactive,pending',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $status = $request->input('status');
        $vendor->is_active = $status;
        $vendor->save();

        return response()->json([
            'message' => 'Vendor status updated successfully',
            'vendor' => [
                'id' => $vendor->id,
                'name' => $vendor->full_name,
                'store_name' => $vendor->store_name,
                'status' => $vendor->is_active,
            ]
        ]);
    }

    public function destroy($id)
    {
        $admin = auth('admin')->user();
        if (!$admin) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $vendor = Vendor::with(['orders'])->find($id);
        if (!$vendor) {
            return response()->json(['error' => 'Vendor not found'], 404);
        }

        if ($vendor->orders()->count() > 0) {
            $vendor->is_active = 'inactive';
            $vendor->save();
            return response()->json(['error' => 'Vendor has associated orders and cannot be deleted. he is inactive now'], 409);
        }

        if ($vendor->commissionPlans()->exists()) {
            $vendor->commissionPlans()->delete();
        }

        $vendor->delete();

        return response()->json(['message' => 'Vendor deleted successfully']);
    }


    public function addCommissionPlans($id, Request $request)
    {
        $admin = auth('admin')->user();
        if (!$admin) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        if (!Vendor::whereKey($id)->exists()) {
            return response()->json(['error' => 'Vendor not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'commission_type' => 'required|string|in:fixed,variable',
        ]);

        if ($validator->fails()){
            return response()->json(['errors' => $validator->errors()], 422);
        }

        if ($request->input('commission_type') === 'fixed') {
            $validator = Validator::make($request->all(), [
                'fixed_percentage' => 'required|numeric|min:0|max:100',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $commissionPlan = new CommissionPlan();
            $commissionPlan->vendor_id = $id;
            $commissionPlan->commission_type = 'fixed';
            $commissionPlan->fixed_percentage = $request->input('fixed_percentage');
            $commissionPlan->save();

            return response()->json([
                'message' => 'Fixed commission plan added successfully',
                'commission_plan' => $commissionPlan
            ]);
        } else {
            $validator = Validator::make($request->all(), [
                'ranges' => 'required|array',
                'ranges.*.plan_name' => 'required|string|max:255',
                'ranges.*.product_category_id' => 'required|exists:main_categories,id',
                'ranges.*.min_value' => 'required|numeric|min:0',
                'ranges.*.max_value' => 'required|numeric|min:0',
                'ranges.*.percentage' => 'required|numeric|min:0|max:100',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $commissionPlan = new CommissionPlan();
            $commissionPlan->vendor_id = $id;
            $commissionPlan->commission_type = 'variable';
            $commissionPlan->fixed_percentage = null;
            $commissionPlan->save();

            foreach ($request->input('ranges') as $range) {
                if ($range['min_value'] >= $range['max_value']) {
                    return response()->json(['error' => 'max_value must be greater than min_value'], 422);
                }
                $commissionPlan->ranges()->create([
                    'plan_name' => $range['plan_name'],
                    'product_category_id' => $range['product_category_id'],
                    'min_value' => $range['min_value'],
                    'max_value' => $range['max_value'],
                    'percentage' => $range['percentage'],
                ]);
            }


            return response()->json([
                'message' => 'Commission plans added successfully',
                'commission_plans' => $commissionPlan->load('ranges')
            ]);
        }
    }

    public function updateCommissionPlans($id, $planId, Request $request)
    {
        $admin = auth('admin')->user();
        if (!$admin) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        if (!Vendor::whereKey($id)->exists()) {
            return response()->json(['error' => 'Vendor not found'], 404);
        }

        $commissionPlan = CommissionPlan::with('ranges')->where('vendor_id', $id)->find($planId);
        if (!$commissionPlan) {
            return response()->json(['error' => 'Commission plan not found'], 404);
        }

        $validated = $request->validate([
            'commission_type' => 'required|string|in:fixed,variable',
        ]);

        if ($request->input('commission_type') === 'fixed') {
            $validator = Validator::make($request->all(), [
                'fixed_percentage' => 'required|numeric|min:0|max:100',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $commissionPlan->commission_type = 'fixed';
            $commissionPlan->fixed_percentage = $request->input('fixed_percentage');
            $commissionPlan->save();

            return response()->json([
                'message' => 'Fixed commission plan updated successfully',
                'data' => $commissionPlan
            ]);
        } else {
            $validator = Validator::make($request->all(), [
                'ranges' => 'required|array',
                'ranges.*.plan_name' => 'required|string|max:255',
                'ranges.*.product_category_id' => 'required|exists:main_categories,id',
                'ranges.*.min_value' => 'required|numeric|min:0',
                'ranges.*.max_value' => 'required|numeric|min:0',
                'ranges.*.percentage' => 'required|numeric|min:0|max:100',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            foreach ($request->input('ranges') as $range) {
                if ($range['min_value'] >= $range['max_value']) {
                    return response()->json(['error' => 'max_value must be greater than min_value'], 422);
                }
            }

            DB::transaction(function () use ($commissionPlan, $request) {
                $commissionPlan->update([
                    'commission_type' => 'variable',
                    'fixed_percentage' => null
                ]);
                $commissionPlan->ranges()->delete();

                $rangesData = [];
                foreach ($request->input('ranges') as $range) {
                    $rangesData[] = [
                        'commission_plan_id' => $commissionPlan->id,
                        'plan_name' => $range['plan_name'],
                        'product_category_id' => $range['product_category_id'],
                        'min_value' => $range['min_value'],
                        'max_value' => $range['max_value'],
                        'percentage' => $range['percentage'],
                        'created_at' => now(),
                        'updated_at' => now()
                    ];
                }

                CommissionRange::insert($rangesData);
            });

            return response()->json([
                'message' => 'Commission plans updated successfully',
                'data' => $commissionPlan->load('ranges')
            ]);
        }
    }

    public function deleteCommissionPlan($id, $planId)
    {
        $admin = auth('admin')->user();
        if (!$admin) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        if (!Vendor::where('id', $id)->exists()) {
            return response()->json(['error' => 'Vendor not found'], 404);
        }

        $commissionPlan = CommissionPlan::where('vendor_id', $id)->find($planId);
        if (!$commissionPlan) {
            return response()->json(['error' => 'Commission plan not found'], 404);
        }

        $commissionPlan->delete();

        return response()->json(['message' => 'Commission plan deleted successfully']);
    }
}