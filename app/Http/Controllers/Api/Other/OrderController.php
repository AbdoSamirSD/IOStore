<?php

namespace App\Http\Controllers\Api\Other;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\PromoCode;
use Illuminate\Http\Request;
use Validator;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'promo_code_id' => 'nullable|exists:promo_codes,id',
        ]);

        $userId = auth()->id();
        if (!$userId) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $items = collect($validated['items']);
        $productIds = $items->pluck('product_id')->unique();

        // Eager load images with products
        $products = Product::with('images')->whereIn('id', $productIds)->get()->keyBy('id');

        $firstProduct = $products[$items->first()['product_id']] ?? null;
        if (!$firstProduct) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        $vendorId = $firstProduct->vendor_id;

        $productDiscount = 0;
        $promoCodeDiscount = 0;
        $subTotal = 0;
        $deliveryFee = 50;

        foreach ($items as $item) {
            $product = $products[$item['product_id']] ?? null;

            if (!$product) {
                return response()->json(['message' => "Product with ID {$item['product_id']} not found"], 404);
            }

            if ($product->vendor_id !== $vendorId) {
                return response()->json(['message' => 'All products must belong to the same vendor'], 422);
            }

            if ($product->stock < $item['quantity']) {
                return response()->json(['message' => "Product {$product->name} is out of stock"], 422);
            }

            $productDiscount += $product->discount * $item['quantity'];
            $subTotal += $product->price * $item['quantity'];
        }

        // Promo Code Handling
        $promoCodeId = $request->input('promo_code_id');
        $promoCode = null;

        if ($promoCodeId) {
            $promoCode = PromoCode::where('id', $promoCodeId)
                ->where('status', 'active')
                ->whereDate('start_date', '<=', now())
                ->whereDate('end_date', '>=', now())
                ->first();

            if (!$promoCode) {
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
        ]);

        $orderItems = $items->map(function ($item) use ($products, $order) {
            $product = $products[$item['product_id']];
            return [
                'order_id' => $order->id,
                'product_id' => $product->id,
                'name' => $product->name,
                'price' => $product->price,
                'image' => optional($product->images->first())->url ?? null,
                'quantity' => $item['quantity'],
            ];
        });

        OrderItem::insert($orderItems->toArray());

        if ($promoCode) {
            $promoCode->increment('uses');
        }

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
                'items' => $order->items->map(function ($item) {
                    return [
                        'product_id' => $item->product_id,
                        'product_name' => $item->product->name,
                        'image' => $item->image,
                        'quantity' => $item->quantity,
                        'price' => $item->price,
                    ];
                }),
                'created_at' => $order->created_at->toDateTimeString(),
                'updated_at' => $order->updated_at->toDateTimeString(),
            ]
        ], 201);
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
        if (!in_array($order->status, ['on_the_way', 'canceled']) && $order->user_id == $authUserId) {
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
