<?php

namespace App\Http\Controllers\Api\Admin\Orders;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderStatusLog;
use Illuminate\Http\Request;
use Validator;

class OrdersController extends Controller
{
    public function index(Request $request)
    {
        $admin = auth('admin')->user();
        if (!$admin) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        $orders = Order::with('promoCode', 'user', 'vendor')->latest()->paginate(20);

        $orders->getCollection()->transform(function ($order) {
            return [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'status' => $order->status,
                'total' => $order->total_cost,
                'created_at' => $order->created_at->format('Y-m-d H:i:s'),
                'customer' => $order->user ? [
                    'id' => $order->user->id,
                    'name' => $order->user->name,
                ] : null,
                'vendor' => $order->vendor ? [
                    'id' => $order->vendor->id,
                    'name' => $order->vendor->full_name,
                ] : null,
                'promo_code' => $order->promoCode,
            ];
        });

        $count = $orders->total();

        return response()->json([
            'message' => 'Orders retrieved successfully',
            'total' => $count,
            'data' => $orders,
        ]);
    }

    public function show($id)
    {
        $admin = auth('admin')->user();
        if (!$admin) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $order = Order::with('items', 'promoCode', 'user', 'vendor')->find($id);
        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        return response()->json([
            'message' => 'Order retrieved successfully',
            'data' => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'status' => $order->status,
                'total' => $order->total_cost,
                'created_at' => $order->created_at->format('Y-m-d H:i:s'),
                'items' => $order->items,
                'customer' => $order->user ? [
                    'id' => $order->user->id,
                    'name' => $order->user->name,
                ] : null,
                'vendor' => $order->vendor ? [
                    'id' => $order->vendor->id,
                    'name' => $order->vendor->full_name,
                ] : null,
                'promo_code' => $order->promoCode,
            ]
        ]);
    }

    public function updateStatus(Request $request, $id)
    {
        $admin = auth('admin')->user();
        if (!$admin) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:on_the_way,delivered',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $order = Order::find($id);

        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        if ($order->status === 'cancelled') {
            return response()->json(['message' => 'Order is canceled.'], 400);
        }

        $newStatus = $request->input('status');
        if ($newStatus === 'on_the_way' && $order->status !== 'completed') {
            return response()->json(['message' => 'Order must be completed before being on the way.'], 400);
        }

        if ($newStatus === 'delivered' && $order->status !== 'on_the_way') {
            return response()->json(['message' => 'Order must be on the way before being delivered.'], 400);
        }

        $order->status = $newStatus;
        $order->save();

        // update status logs table
        OrderStatusLog::create([
            'order_id' => $order->id,
            'status' => $order->status,
            'source' => 'admin',
            'updated_by' => $admin->id,
        ]);

        return response()->json([
            'message' => 'Order status updated successfully',
            'data' => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'status' => $order->status,
                'total' => $order->total_cost,
                'created_at' => $order->created_at->format('Y-m-d H:i:s'),
                'items' => $order->items,
                'customer' => $order->user ? [
                    'id' => $order->user->id,
                    'name' => $order->user->name,
                ] : null,
                'vendor' => $order->vendor ? [
                    'id' => $order->vendor->id,
                    'name' => $order->vendor->full_name,
                ] : null,
                'promo_code' => $order->promoCode,
            ]
        ]);
    }

    public function filterByStatus($status)
    {
        $admin = auth('admin')->user();
        if (!$admin) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $validStatuses = ['accepted', 'preparing', 'pending', 'on_the_way', 'delivered', 'completed', 'cancelled'];
        if (!in_array($status, $validStatuses)) {
            return response()->json(['message' => 'Invalid status'], 400);
        }

        $orders = Order::where('status', $status)->latest()->paginate(20);

        if ($orders->isEmpty()) {
            return response()->json(['message' => 'No orders found for the given status', 'data' => []], 404);
        }

        $orders->getCollection()->transform(function ($order) {
            return [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'status' => $order->status,
                'total' => $order->total_cost,
                'created_at' => $order->created_at->format('Y-m-d H:i:s'),
                'customer' => $order->user ? [
                    'id' => $order->user->id,
                    'name' => $order->user->name,
                ] : null,
                'vendor' => $order->vendor ? [
                    'id' => $order->vendor->id,
                    'name' => $order->vendor->full_name,
                ] : null,
                'promo_code' => $order->promoCode,
            ];
        });
        $count = $orders->total();

        return response()->json([
            'message' => 'Orders retrieved successfully',
            'total' => $count,
            'data' => $orders,
        ]);
    }

    public function search(Request $request)
    {
        $admin = auth('admin')->user();
        if (!$admin) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $query = $request->input('query');
        if (!$query) {
            return response()->json(['message' => 'Query is required'], 400);
        }

        $orders = Order::with('user', 'vendor', 'promoCode', 'items')
        ->where(function ($q) use ($query) {
            $q->where('order_number', 'like', "%{$query}%")
                ->orWhere('total_cost','like', "%{$query}%")
                ->orWhere('status', 'like', "%{$query}%")

                ->orWhereHas('user', function ($q) use ($query) {
                    $q->where('name', 'like', "%{$query}%");
                })
                ->orWhereHas('vendor', function ($q) use ($query) {
                    $q->where('full_name', 'like', "%{$query}%");
                })
                ->orWhereHas('promoCode', function ($q) use ($query) {
                    $q->where('code', 'like', "%{$query}%");
                })
                ->orWhereHas('items', function ($q) use ($query) {
                    $q->where('name', 'like', "%{$query}%");
                });
        })
            ->latest()
            ->paginate(20);

        $count = $orders->total();
        if ($orders->isEmpty()) {
            return response()->json(['message' => 'No orders found for the given query'], 404);
        }

        $orders->getCollection()->transform(function ($order) {
            return [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'status' => $order->status,
                'total' => $order->total_cost,
                'created_at' => $order->created_at->format('Y-m-d H:i:s'),
                'customer' => $order->user ? [
                    'id' => $order->user->id,
                    'name' => $order->user->name,
                ] : null,
                'vendor' => $order->vendor ? [
                    'id' => $order->vendor->id,
                    'name' => $order->vendor->full_name,
                ] : null,
                'items' => $order->items->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'name' => $item->name,
                    ];
                }),
                'promo_code' => $order->promoCode,
            ];
        });
        return response()->json([
            'message' => 'Orders retrieved successfully',
            'total' => $count,
            'data' => $orders,
        ]);
    }

    public function filterByDateRange(Request $request)
    {
        $admin = auth('admin')->user();
        if (!$admin) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $validator = Validator::make($request->all(), [
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $orders = Order::whereBetween('created_at', [$startDate, $endDate])->with('user', 'vendor', 'promoCode')->latest()->paginate(20);
        $orders->getCollection()->transform(function ($order) {
            return [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'status' => $order->status,
                'total' => $order->total_cost,
                'created_at' => $order->created_at->format('Y-m-d H:i:s'),
                'customer' => $order->user ? [
                    'id' => $order->user->id,
                    'name' => $order->user->name,
                ] : null,
                'vendor' => $order->vendor ? [
                    'id' => $order->vendor->id,
                    'name' => $order->vendor->full_name,
                ] : null,
                'promo_code' => $order->promoCode,
            ];
        });

        $count = $orders->total();

        return response()->json([
            'message' => 'Orders retrieved successfully',
            'total' => $count,
            'data' => $orders,
        ]);
    }

}