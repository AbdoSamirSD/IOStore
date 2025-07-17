<?php

namespace App\Http\Controllers\Api\Other;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index()
    {
        $cartItems = auth()->user()->cartItems()->with('product')->get();
        $deliveryPrice = 50; // Fixed delivery price

        // Calculate total price including delivery
        $totalPrice = $cartItems->sum(function ($item) {
            return $item->quantity * $item->product->price;
        });

        return response()->json([
            'delivery_fee' => $deliveryPrice,
            'totalPrice_without_delivery' => $totalPrice,
            'totalPrice' => $totalPrice + $deliveryPrice,
            'cartItems' => $cartItems,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);
        $product = Product::find($request->product_id);
        if(!$product){
            return response()->json(['message' => 'product not found'], 404);
        }
        if ($product->stock < $request->quantity) {
            return response()->json(['message' => 'Not enough stock'], 403);
        }
        $cartItem = auth()->user()->cartItems()->updateOrCreate([
            'product_id' => $request->product_id,
            'quantity' => $request->quantity
        ]);
        return response()->json($cartItem);
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);
        $cartItem = CartItem::find($id);
        $product = Product::find($cartItem->product_id);
        if ($product->stock < $data['quantity']) {
            return response()->json(['message' => 'Not enough stock'], 403);
        }
        $cartItem->update($data);
        return response()->json($cartItem);
    }

    public function destroy($id)
    {
        $cartItem = auth()->user()->cartItems()->find($id);
        $cartItem->delete();
        return response()->json($cartItem);
    }

    public function destroyAll()
    {
        auth()->user()->cartItems()->delete();
        return response()->json();
    }
}
