<?php

namespace App\Http\Controllers\Api\Other;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\PromoCode;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'items' => 'required|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'delivery_fee' => 'required|numeric|min:0',
            'promo_code_id' => 'nullable|exists:promo_codes,id',
        ]);
        $userId = auth()->id();
        $orderNumber = uniqid(prefix: 'so-E-' . now()->year . '-');

        // Calculate subtotal
        $subTotal = 0;
        foreach ($request->items as $item) {
            $product = Product::findOrFail($item['product_id']);

            if ($product->vendor->status === false) {
                return response()->json(['message' => 'المتجر الخاص بالمنتج مغلق حاليًا ولا يمكن تنفيذ الطلب.'], 403);
            }

            $subTotal += $product->price * $item['quantity'];
        }

        // Fetch delivery fee and promo code details
        $deliveryFee = $request->delivery_fee;
        $promoCode = null;
        $discount = 0;

        if ($request->promo_code_id) {
            $promoCode = PromoCode::find($request->promo_code_id);
            if ($promoCode->is_valid) {
                $discount = $promoCode->discount;
                $promoCode->uses++;
                $promoCode->save();
            } else {
                return response()->json(['message' => 'Invalid promo code'], 403);
            }
        }

        $totalCost = max(0, $subTotal + $deliveryFee - $discount);
        // Create the order
        $order = Order::create([
            'user_id' => $userId,
            'order_number' => $orderNumber,
            'sub_total' => $subTotal,
            'delivery_fee' => $deliveryFee,
            'discount' => $discount,
            'total_cost' => $totalCost,
            'promo_code_id' => $request->promo_code_id,
        ]);

        // Process order items
        foreach ($request->items as $item) {
            $product = Product::find($item['product_id']);
            $price = $product->price;

            // Create order item
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'price' => $price,
                'name' => $product->name,
                'main_category_name' => $product->mainCategory()->get()->first()->name ?? '',
                'image' => $product->images()->first()->url ?? null,
            ]);
        }
        auth()->user()->cartItems()->delete();
        return response()->json($order);
    }
    public function index()
    {
        $userId = auth()->id();
        $orders = Order::where('user_id', $userId)
            ->with(['items.product', 'promoCode'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($orders);
    }

    public function show($id)
    {
        $order = Order::with('items.product', 'promoCode')->find($id);

        return response()->json($order);
    }

    public function cancel($id)
    {
        $authUserId = auth()->id();
        $order = Order::findOrFail($id);
        if ($order->status == 'preparing' && $order->user_id == $authUserId) {
            $order->update(['status' => 'canceled']);
            $order->refresh();
            return response()->json($order);
        } else {
            return response()->json(['message'
            => 'لا يمكن الغاء الطلب'], 403);
        }
    }
}
