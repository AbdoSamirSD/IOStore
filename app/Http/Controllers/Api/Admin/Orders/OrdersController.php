<?php

namespace App\Http\Controllers\Api\Admin\Orders;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class OrdersController extends Controller
{
    public function index()
    {
        $orders = Order::with('items', 'promoCode')->latest()->paginate(50, );

        return response()->json($orders);
    }

    public function show($id)
    {
        $order = Order::with('items', 'promoCode')->findOrFail($id);

        return response()->json($order);
    }

    // change status ['preparing', 'on the way', 'delivered', 'canceled']
    public function changeStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:preparing,on the way,delivered,canceled',
        ]);
        $order = Order::findOrFail($id);
        $order->status = $request->status;
        $order->save();
        return response()->json($order);
    }

    

}
