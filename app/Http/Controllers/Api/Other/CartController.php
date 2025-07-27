<?php

namespace App\Http\Controllers\Api\Other;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index(){
        $user = auth()->user();
        if(!$user){
            return response()->json([
                'message' => 'unauthenticated'
            ], 401);
        }
        $cartItems = $user->cartItems()->with('product')->get();
        if($cartItems->isEmpty()){
            return response()->json([
                'message' => 'Cart is empty'
            ], 400);
        }
        
        $vendor = $cartItems->pluck('product.vendor_id')->unique();
        if ($vendor->count() > 1) {
            return response()->json([
                'message' => 'You can only order from one vendor at a time'
            ], 400);
        }

        foreach($cartItems as $item){
            if ($item->product->stock < $item->quantity){
                return response()->json([
                    'message' => $item->product->name . ' is out of stock or not enough stock available'
                ], 400);
            }
        }
        
        // Calculate total price including delivery
        $deliveryPrice = 50;
        $totalPrice = 0;
        foreach($cartItems as $item){
            $price = $item->product->price;
            $discount = $item->product->discount;
            $finalPrice = max(0, $price - $discount);
            
            $totalPrice += $finalPrice * $item->quantity;
        }

        return response()->json([
            'items' => $cartItems->map(function ($item) {

                return [
                    'product_name' => $item->product->name,
                    'product_image' => $item->product->images->first(),
                    'price' => $item->product->price,
                    'discount' => $item->product->discount,
                    'final_price' => max(0, $item->product->price - $item->product->discount),
                    'quantity' => $item->quantity,
                    'total_price' => max(0, ($item->product->price - $item->product->discount) * $item->quantity),
                ];
            }),
            'delivery_fee' => $deliveryPrice,
            'total_price' => $totalPrice + $deliveryPrice,
        ], 200);
    }
    
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);
        $product = Product::find($request->product_id);
        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        if ($product->stock < $request->quantity) {
            return response()->json(['message' => 'Not enough stock'], 403);
        }

        $cartItem = auth()->user()->cartItems()->updateOrCreate(
            ['product_id' => $request->product_id],
            ['quantity' => $request->quantity]
        );
        return response()->json($cartItem);
    }

    public function update(Request $request, $id)
    {
        $cartItem = CartItem::find($id);
        if (!$cartItem){
            return response()->json([
                'message' => 'Item with id '. $id .' not found'
            ]);
        }

        $data = $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);


        if ($cartItem->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $product = Product::find($cartItem->product_id);
        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }
        
        if ($product->stock < $data['quantity']) {
            return response()->json(['message' => 'Not enough stock'], 403);
        }
        
        $cartItem->update($data);
        return response()->json($cartItem);
    }

    public function destroy($id)
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['message' => 'unauthenticated'], 401);
        }
        $cartItem = $user->cartItems()->find($id);
        if (!$cartItem) {
            return response()->json(['message' => 'Item not found'], 404);
        }
        if ($cartItem->user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        $product = Product::find($cartItem->product_id);
        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }
        $cartItem->delete();
        return response()->json([
            'message' => 'Item removed from cart',
            $cartItem
        ]);
    }

    public function destroyAll()
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['message' => 'unauthenticated'], 401);
        }
        if ($user->cartItems()->count() === 0) {
            return response()->json(['message' => 'Cart is already empty'], 400);
        }
        // Delete all cart items for the authenticated user
        $user->cartItems()->delete();

        return response()->json([
            'message' => 'All items removed from cart',
        ]);
    }
}
