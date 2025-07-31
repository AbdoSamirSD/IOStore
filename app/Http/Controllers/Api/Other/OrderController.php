<?php

namespace App\Http\Controllers\Api\Other;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\PromoCode;
use App\Models\CommissionPlan;
use App\Models\Vendor;
use App\Models\MainCategory;
use App\Models\CommissionRange;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    public function store(Request $request)
    {
        try {
            return DB::transaction(function () use ($request) {
                $validated = $request->validate([
                    'items' => 'required|array|min:1',
                    'items.*.product_id' => 'required|exists:products,id',
                    'items.*.quantity' => 'required|integer|min:1',
                    'promo_code' => 'nullable|exists:promo_codes,code',
                ]);

                $userId = auth()->id();
                if (!$userId) {
                    return response()->json(['message' => 'Unauthorized'], 401);
                }

                $items = collect($validated['items']);
                $productIds = $items->pluck('product_id')->unique();
                $products = Product::with('images')->whereIn('id', $productIds)->get()->keyBy('id');
                $allVendorIds = $products->pluck('vendor_id')->unique();
                if ($allVendorIds->count() > 1) {
                    return response()->json(['message' => 'All products must belong to the same vendor'], 422);
                }
                $vendorId = $allVendorIds->first();
                $deliveryFee = 50;
                
                $subTotal = 0;
                $productDiscount = 0;
            
                $totalCost = 0;
                $totalCommission = 0;
        
                $orderItems = $items->map(function ($item) use ($products,  &$subTotal, &$productDiscount, &$totalCommission, $vendorId) {
                    $product = $products[$item['product_id']];
                    $quantity = $item['quantity'];
                    $stockDecremented = Product::where('id', $product->id)
                        ->where('stock', '>=', $quantity)
                        ->decrement('stock', $quantity);

                    if (!$stockDecremented) {
                        throw ValidationException::withMessages([
                            'items' => ["Product {$product->name} is out of stock"]
                        ]);
                    }

                    $productTotal = $product->price * $quantity;
                    $commissionPlan = CommissionPlan::with('ranges')
                        ->where('vendor_id', $vendorId)
                        ->where('product_category_id', $product->main_category_id)
                        ->first();
                    
                    $productCommission = 0;
                    $commissionPercentage = 0;

                    if($commissionPlan){
                        if($commissionPlan->commission_type === 'fixed'){
                            $commissionPercentage = $commissionPlan->fixed_percentage;
                        }
                        elseif ($commissionPlan->commission_type === 'variable') {
                            $range = $commissionPlan->ranges
                                ->where('min_value', '<=', $product->price)
                                ->where('max_value', '>=', $product->price)
                                ->first();

                            if($range){
                                $commissionPercentage = $range->percentage?? 10;
                            }else {
                                $commissionPercentage = 10; // Default commission if no range found
                            }
                        }
                        $productCommission = $product->price * ($commissionPercentage / 100) * $quantity;
                        $totalCommission += $productCommission;
                    }

                    $subTotal += $productTotal;
                    $productDiscount += $product->discount * $quantity;
                    return [
                        'product_id' => $product->id,
                        'name' => $product->name,
                        'price' => $product->price,
                        'image' => optional($product->images->first())->url ?? null,
                        'quantity' => $item['quantity'],
                        'product_commission' => $productCommission,
                    ];
                });
                
                        // Promo Code Handling 
                $promoCode = PromoCode::where('code', $request->input('promo_code'))->first();
                $promoCodeDiscount = 0;
                if ($promoCode) {
                    if (!$promoCode->status || !now()->between($promoCode->start_date, $promoCode->end_date)) {
                        return response()->json(['message' => 'Promo code is not valid'], 422);
                    }

                    if (!is_null($promoCode->max_uses) && $promoCode->uses >= $promoCode->max_uses) {
                        return response()->json(['message' => 'Promo code usage limit reached'], 422);
                    }
                    
                    $promoCodeDiscount = $promoCode->type === 'fixed'
                        ? $promoCode->value
                        : ($subTotal * $promoCode->value) / 100;
                }

                $totalCost = $subTotal + $deliveryFee - $productDiscount - $promoCodeDiscount;
                
                $order = Order::create([
                    'user_id' => $userId,
                    'vendor_id' => $vendorId,
                    'order_number' => uniqid('ORD-'),
                    'sub_total' => $subTotal,
                    'delivery_fee' => $deliveryFee,
                    'discount' => $productDiscount + $promoCodeDiscount,
                    'total_cost' => $totalCost,
                    'promo_code_id' => $promoCode?->id,
                    'status' => 'pending',
                    'order_commission' => $totalCommission
                ]);

                $orderItems->transform(function ($item) use ($order) {
                    return array_merge(['order_id' => $order->id], $item);
                });
                OrderItem::insert($orderItems->toArray());

                if ($promoCode) {
                    $promoCode->increment('uses');
                }

                $order->load('items.product');
                return response()->json([
                    'message' => 'Order Created Successfully.',
                    'order' => [
                        'order_number' => $order->order_number,
                        'id' => $order->id,
                        'status' => $order->status,
                        'sub_total' => $order->sub_total,
                        'delivery_fee' => $order->delivery_fee,
                        'discount' => $order->discount,
                        'total_cost' => $order->total_cost,
                        'promo_code' => $order->promoCode ? $order->promoCode->code : null,
                        'items' => $order->items->map(fn ($item) => [
                            'product_id' => $item->product_id,
                            'product_name' => $item->product->name,
                            'image' => $item->image,
                            'quantity' => $item->quantity,
                            'price' => $item->price,
                        ]),
                        'order_commission' => $order->order_commission,
                        'created_at' => $order->created_at->toDateTimeString(),
                        'updated_at' => $order->updated_at->toDateTimeString(),
                    ]
                ], 201);
            });
        } catch (ValidationException $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while creating the order.',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function index()
    {
        $userId = auth()->id();
        if(!$userId){
            return response()->json([
                'message' => 'Unauthorized.'
            ], 401);
        }
        $orders = Order::where('user_id', $userId)
            ->with(['items.product', 'promoCode'])
            ->orderBy('created_at', 'desc')
            ->get();
        if ($orders === null || $orders->isEmpty()) {
            return response()->json([
                'message' => 'No orders Found.',
            ], 404);
        }
        return response()->json([
            'message' => 'Orders retrieved successfully.',
            'data' => [
                'orders' => $orders->map(function ($order) {
                    return [
                        'id' => $order->id,
                        'order_number' => $order->order_number,
                        'status' => $order->status,
                        'total_cost' => $order->total_cost,
                        'items' => $order->items->map(function ($item) {
                            return [
                                'product_id' => $item->product_id,
                                'product_name' => $item->product->name,
                                'quantity' => $item->quantity,
                                'price' => $item->price,
                            ];
                        }),
                        'created_at' => $order->created_at->toDateTimeString(),
                    ];
                })
            ]
        ], 200);
    }

    public function show($id)
    {
        $order = Order::with('items.product', 'promoCode')->find($id);
        if(!$order){
            return response()->json(['message' => 'Order not found'], 404);
        }


        return response()->json([
            'id' => $order->id,
            'order_nember' => $order->order_number,
            'status' => $order->status,
            'sub_total' => $order->sub_total,
            'deliver_fee' => $order->delivery_fee,
            'discount' => $order->discount,
            'total_cost' => $order->total_cost,
            'promo_code' => $order->promoCode ? $order->promoCode->code : null,
            'items' => $order->items->map(function ($item) {
                return [
                    'product_id' => $item->product_id,
                    'product_name' => $item->product->name,
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                    'image' => $item->image,
                    'discount' => $item->product->discount,
                ];
            }),
            'created_at' => $order->created_at->toDateTimeString(),
            'updated_at' => $order->updated_at->toDateTimeString(),
        ]);
    }

    public function cancel($id)
    {
        $authUserId = auth()->id();
        if (!$authUserId){
            return response()->json([
                'message' => 'Unauthorized'
            ], 401);
        }
        $order = Order::findOrFail($id);
        if (!in_array($order->status, ['on_the_way', 'cancelled']) && $order->user_id == $authUserId) {
            $order->update(['status' => 'cancelled']);
            $order->refresh();
            return response()->json([
                'message' => 'Order cancelled successfully.',
            ], 200);
        } else {
            return response()->json([
                'message' => 'Cannot cancel this order.'],
                403);
        }
    }
}
