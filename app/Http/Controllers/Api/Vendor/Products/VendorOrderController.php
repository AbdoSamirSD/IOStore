<?php

namespace App\Http\Controllers\Api\Vendor\Products;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use Illuminate\Support\Facades\DB;

class VendorOrderController extends Controller
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
        $order = Order::with(['items', 'user', 'statusLogs' => function ($query) {
                $query->orderBy('status_changed_at', 'desc');
            }])
            ->where('vendor_id', $vendor->id)
            ->where('id', $order_id)
            ->first();

        if (!$order) {
            return response()->json([
                'message' => "Order {$order_id} not found.",
            ], 404);
        }

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
            ->first();

        if (!$order) {
            return response()->json([
                'message' => "Order {$order_id} not found.",
            ], 404);
        }

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

    public function statistics()
    {
        $vendor = auth()->user();
        if (!$vendor) {
            return response()->json([
                'message' => 'Vendor not authenticated.',
            ], 401);
        }

        $statistics = Order::where('vendor_id', $vendor->id)
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->get();

        return response()->json([
            'message' => 'Order statistics retrieved successfully.',
            'data' => $statistics,
        ]);
    }

    public function getAllStatuses()
    {
        $type = DB::select("SHOW COLUMNS FROM orders WHERE Field = 'status'");
        $columnType = $type[0]->Type;

        // Extract the enum values using regex
        preg_match('/enum\((.*)\)/', $columnType, $matches);
        $enumValues = [];

        if (isset($matches[1])) {
            $enumValues = explode(',', str_replace("'", '', $matches[1]));
        }

        return response()->json([
            'message' => 'All order statuses retrieved successfully.',
            'data' => $enumValues,
        ]);

    }
}
