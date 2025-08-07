<?php

namespace App\Http\Controllers\Api\Vendor\Products;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Validator;

class VendorOrderController extends Controller
{
    public function index(Request $request)
    {
        $vendor = $request->user();
        if (!$vendor){
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $query = Order::where('vendor_id', $vendor->id);
        // Optionally, you can add filters based on request parameters
        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->has('date_from') && $request->has('date_to')) {
            $query->whereBetween('created_at', [
                $request->input('date_from'),
                $request->input('date_to')
            ]);
        }
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $orders = $query->with(['items', 'user', 'statusLogs' => function ($query) {
            $query->orderBy('status_changed_at', 'desc');
        }])->orderBy('created_at', 'desc')->paginate(10);

        return response()->json([
            'message' => 'Vendor orders retrieved successfully.',
            'data' => $orders,
        ]);
    }

    public function showOrder($order_id)
    {
        $vendor = auth()->user();
        if (!$vendor) {
            return response()->json([
                'message' => 'Vendor not authenticated.',
            ], 401);
        }
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
        $validator = Validator::make(
            $request->all(),[
                'status' => 'required|string|in:pending,accepted,preparing,completed,cancelled',
            ]
        );

        if ($validator->fails()){
            return response()->json(
                [
                    'message' => 'Validator Error',
                    'errors' => $validator -> errors(),
                ], 422
            );
        }

        $vendor = auth()->user();
        if (!$vendor) {
            return response()->json([
                'message' => 'Vendor not authenticated.',
            ], 401);
        }

        
        $order = Order::where('vendor_id', $vendor->id)
        ->where('id', $order_id)
        ->first();
        
        if (!$order) {
            return response()->json([
                'message' => "Order {$order_id} not found.",
            ], 404);
        }
 
        // Check if the status is already set to the requested status
        if ($order->status === $request->input('status')) {
            return response()->json([
                'message' => "Order {$order_id} is already in the status: {$order->status}.",
            ]);
        }

        if ($order->status === 'delivered' || $order->status === 'cancelled') {
            return response()->json([
                'message' => "Order {$order_id} cannot be updated as it is already delivered or cancelled.",
            ], 400);
        }

        $lastLog = $order->statusLogs()->latest()->first();
        if ($lastLog && $lastLog->status === $request->input('status')) {
            return response()->json([
                'message' => "Order {$order_id} is already in the status: {$lastLog->status}.",
            ]);
        }

        $order->status = $request->input('status');
        $order->save();

        $order->statusLogs()->create([
            'source' => 'vendor',
            'updated_by' => $vendor->id,
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
        if (!$vendor) {
            return response()->json([
                'message' => 'Unauthorized.',
            ], 401);
        }
        $filteredOrders = Order::with('items')
            ->where('vendor_id', $vendor->id)
            ->where('status', $status)
            ->paginate(20);

        return response()->json([
            'message' => "Orders filtered by status: {$status}.",
            'data' => $filteredOrders,
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
