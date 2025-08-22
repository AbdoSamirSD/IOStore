<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\Vendor;
use App\Models\User;

class ReportController extends Controller
{
    public function index(){
        $admin = auth('admin')->user();
        if (!$admin) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $vendorStats = Vendor::selectRaw('
            COUNT(*) as total_vendors,
            SUM(CASE WHEN is_active = "active" THEN 1 ELSE 0 END) as active_vendors,
            SUM(CASE WHEN is_active = "pending" THEN 1 ELSE 0 END) as pending_vendors,
            SUM(CASE WHEN is_active = "inactive" THEN 1 ELSE 0 END) as inactive_vendors
        ')->first();

        $productStats = Product::selectRaw('
            COUNT(*) as total_products,
            SUM(CASE WHEN stock > 0 THEN 1 ELSE 0 END) as in_stock,
            SUM(CASE WHEN stock = 0 THEN 1 ELSE 0 END) as out_of_stock,
            SUM(CASE WHEN status = "pending" THEN 1 ELSE 0 END) as pending_products
        ')->first();

        $orderStats = Order::selectRaw('
            COUNT(*) as total_orders,
            SUM(CASE WHEN status = "delivered" THEN 1 ELSE 0 END) as delivered_orders,
            SUM(CASE WHEN status = "cancelled" THEN 1 ELSE 0 END) as cancelled_orders,
            SUM(CASE WHEN status IN ("pending", "accepted", "preparing", "completed") THEN 1 ELSE 0 END) as pending_orders,
            SUM(total_cost) as total_revenue,
            AVG(total_cost) as average_order_value,
            SUM(order_commission) as total_commission
        ')->first();

        $userStats = User::selectRaw('
            COUNT(*) as total_users
        ')->first();

        return response()->json([
            'message' => 'Statistics retrieved successfully.',
            'data' => [
                'vendors' => [
                    'total' => $vendorStats->total_vendors ?? 0,
                    'active' => $vendorStats->active_vendors ?? 0,
                    'pending' => $vendorStats->pending_vendors ?? 0,
                    'inactive' => $vendorStats->inactive_vendors ?? 0,
                    'top_vendors_revenue' => Vendor::select('id', 'full_name', 'store_name')
                        ->withSum('orders', 'total_cost')
                        ->orderBy('orders_sum_total_cost', 'desc')
                        ->take(5)
                        ->get()
                ],
                'products' => [
                    'total' => $productStats->total_products ?? 0,
                    'in_stock' => $productStats->in_stock ?? 0,
                    'out_of_stock' => $productStats->out_of_stock ?? 0,
                    'top_selling' => Product::withCount('orders')
                        ->orderBy('orders_count', 'desc')
                        ->take(5)
                        ->get()->map(function ($product) {
                            return [
                                'id' => $product->id,
                                'name' => $product->name,
                                'orders_count' => $product->orders_count,
                            ];
                        }),
                    'pending_products' => $productStats->pending_products ?? 0
                ],
                'orders' => [
                    'total' => $orderStats->total_orders ?? 0,
                    'pending' => $orderStats->pending_orders ?? 0,
                    'delivered' => $orderStats->delivered_orders ?? 0,
                    'canceled' => $orderStats->cancelled_orders ?? 0,
                    'total_revenue' => (float) $orderStats->total_revenue ?? 0,
                    'order_average' => (float) $orderStats->average_order_value ?? 0,
                    'orders_last_week' => Order::where('created_at', '>=', now()->subWeek())->count(),
                ],
                'commission' => [
                    'total' => $orderStats->total_commission ?? 0,
                    'average_per_order' => $orderStats->delivered_orders > 0 ? $orderStats->total_commission / $orderStats->delivered_orders : 0,
                    'top_vendors' => Vendor::select('id', 'full_name', 'store_name')
                        ->withSum('orders', 'order_commission')
                        ->orderBy('orders_sum_order_commission', 'desc')
                        ->take(5)
                        ->get()
                ],
                'users' => [
                    'total' => $userStats->total_users ?? 0,
                    'users_make_orders_last_month' => User::whereHas('orders', function ($query) {
                        $query->where('created_at', '>=', now()->subMonth());
                    })->count()
                ]
            ],
        ]);
    }
}
