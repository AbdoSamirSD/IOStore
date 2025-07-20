<?php

namespace App\Http\Controllers\Api\Vendor\Products;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $vendor = $request->user();
        $orders = Order::with('items')
            ->where('vendor_id', $vendor->id)
            ->get();

        return response()->json([
            'message' => 'Vendor orders retrieved successfully.',
            'data' => $orders,
        ]);
    }

    public function showOrder($order_id)
    {
        $vendor = auth()->user();
        $order = Order::with('items', 'user', 'statusLogs')
            ->where('vendor_id', $vendor->id)
            ->where('id', $order_id)
            ->firstOrFail();

        return response()->json([
            'message' => "Order {$order_id} retrieved successfully.",
            'data' => $order,
        ]);
    }

    public function updateStatus($order_id, Request $request)
    {
        $request->validate([
            'status' => 'required|string|in:preparing,accepted,on_the_way,delivered,canceled',
        ]);

        $vendor = auth()->user();
        $order = Order::where('vendor_id', $vendor->id)
            ->where('id', $order_id)
            ->firstOrFail();

        $order->status = $request->input('status');
        $order->save();

        $order->statusLogs()->create([
            'status' => $order->status,
            'status_changed_at' => now(),
        ]);

        return response()->json([
            'message' => "Order {$order_id} status updated to {$order->status}.",
            'data' => $order,
        ]);
    }

    public function filterByStatus($status)
    {
        $vendor = auth()->user();
        $filteredOrders = Order::with('items')
            ->where('vendor_id', $vendor->id)
            ->where('status', $status)
            ->get();

        return response()->json([
            'message' => "Orders filtered by status: {$status}.",
            'data' => $filteredOrders,
        ]);
    }
}
