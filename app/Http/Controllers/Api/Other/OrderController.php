<?php

namespace App\Http\Controllers\Api\Other;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\PromoCode;
use Illuminate\Http\Request;
use Validator;

class OrderController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'items' => 'required|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'promo_code_id' => 'nullable|exists:promo_codes,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $userId = auth()->id();
        if (!$userId) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $vendorId = null;
        foreach($request->items as $item){
            $product = Product::find($item['product_id']);
            if ($product) {
                if ($vendorId === null) {
                    $vendorId = $product->vendor_id;
                } elseif ($vendorId !== $product->vendor_id) {
                    return response()->json(['message' => 'All products must belong to the same vendor.'], 403);
                }
            } else {
                return response()->json(['message' => 'Product not found.'], 404);
            }
        }


        $orderNumber = uniqid(prefix: 'so-E-' . now()->year . '-');

        // Calculate subtotal
        $subTotal = 0;
        $productDiscountTotal = 0;

        foreach ($request->items as $item) {
            $product = Product::findOrFail($item['product_id']);

            if ($product->vendor->status === false) {
                return response()->json(['message' => 'Store is closed right now.'], 403);
            }

            $itemTotal = $product->price * $item['quantity'];
            $itemDiscount = $product->discount ? ($product->price * $product->discount / 100) * $item['quantity'] : 0;

            $subTotal += $itemTotal;
            $productDiscountTotal += $itemDiscount;
        }

        // Fetch delivery fee and promo code details
        $deliveryFee = 50;
        $promoCode = null;
        $promoDiscount = 0;

        if ($request->promo_code_id) {
            $promoCode = PromoCode::find($request->promo_code_id);
            if (
                $promoCode && 
                $promoCode->status === 'active' && 
                now()->between($promoCode->start_date, $promoCode->end_date) && 
                $promoCode->uses < $promoCode->max_uses
                )
                {
                    if($promoCode->type === 'percentage'){
                        $promoDiscount = ($subTotal * $promoCode->discount) / 100;
                    }else{
                        $promoDiscount = $promoCode->discount;
                    }

                    $promoDiscount = min($promoDiscount, $subTotal);
                    $promoCode->increment('uses');

            } else {
                return response()->json(['message' => 'Invalid promo code'], 403);
            }
        }

        $totalCost = max(0, $subTotal + $deliveryFee - $productDiscountTotal - $promoDiscount);
        // Create the order
        $order = Order::create([
            'user_id' => $userId,
            'vendor_id' => $vendorId,
            'status' => 'preparing',
            'order_number' => $orderNumber,
            'sub_total' => $subTotal,
            'delivery_fee' => $deliveryFee,
            'discount' => $productDiscountTotal + $promoDiscount,
            'total_cost' => $totalCost,
            'promo_code_id' => $request->promo_code_id,
        ]);

        // Process order items
        foreach ($request->items as $item) {
            $product = Product::find($item['product_id']);
            $price = $product->price;
            $discount = $product->discount ?? 0;

            // Create order item
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'price' => $price,
                'discount' => $discount,
                'name' => $product->name,
                'main_category_name' => $product->mainCategory()->get()->first()->name ?? '',
                'image' => $product->images()->first()->url ?? null,
            ]);
        }
        auth()->user()->cartItems()->delete();

        // $vendor = Vendor::find($vendorId);
        // if ($vendor && $vendor->user){
        //     $vendor->user->notify(new NewOrderNotification($order));
        // }
        return response()->json($order);
    }
    public function index()
    {
        $userId = auth()->id();
        if(!$userId){
            return response()->json([
                'message' => 'Unauthorized'
            ], 401);
        }
        $orders = Order::where('user_id', $userId)
            ->with(['items.product', 'promoCode'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($orders);
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
                ];
            }),
            'created_at' => $order->created_at,
            'updated_at' => $order->updated_at,
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
        if (!in_array($order->status, ['completed', 'on_the_way', 'canceled']) && $order->user_id == $authUserId) {
            $order->update(['status' => 'canceled']);
            $order->refresh();
            return response()->json($order);
        } else {
            return response()->json([
                'message' => 'Cannot cancel this order.'],
                403);
        }
    }
}
