<?php

namespace App\Http\Controllers\Api\Vendor\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Wallet;
use App\Models\Order;
use DB;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $vendor = auth()->user();

        $orderStats = Order::selectRaw("
                COUNT(CASE WHEN status = 'delivered' THEN 1 END) as delivered,
                COUNT(CASE WHEN status = 'cancelled' THEN 1 END) as cancelled,
                COUNT(CASE WHEN status = 'preparing' THEN 1 END) as pending
            ")
            ->where('vendor_id', $vendor->id)
            ->first();

        $productStats = Product::selectRaw("
                COUNT(CASE WHEN status = 'approved' AND is_active = 'active' THEN 1 END) as active_approved,
                COUNT(CASE WHEN status = 'approved' AND is_active = 'inactive' THEN 1 END) as inactive,
                COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending,
                COUNT(CASE WHEN status = 'rejected' THEN 1 END) as rejected
            ")
            ->where('vendor_id', $vendor->id)
            ->first();

        $totalSales = Wallet::where('vendor_id', $vendor->id)->value('total_earnings');

        $lastFiveDeliveredOrders = Order::where('vendor_id', $vendor->id)
            ->where('status', 'delivered')
            ->latest()
            ->take(5)
            ->with(['products:id,name,image']) 
            ->get(['id', 'vendor_id', 'created_at']);

        $salesByMonth = Order::selectRaw("DATE_FORMAT(created_at, '%M') as month, SUM(total_cost) as total")
            ->where('vendor_id', $vendor->id)
            ->where('status', 'delivered')
            ->where('created_at', '>=', now()->subMonths(6))
            ->groupBy('month')
            ->orderByRaw("MIN(created_at)")
            ->get();

        $labels = $salesByMonth->pluck('month');
        $data = $salesByMonth->pluck('total');

        $topSellingProducts = OrderItem::select('order_items.product_id', 
                DB::raw('COUNT(*) as total_sales'),
                'products.name',
                'products.image'    
            )
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->where('orders.status', 'delivered')
            ->where('products.vendor_id', $vendor->id)
            ->groupBy('order_items.product_id', 'products.name', 'products.image')
            ->orderByDesc('total_sales')
            ->take(5)
            ->get();

        return response()->json([
            'orders' => [
                'delivered' => $orderStats->delivered,
                'cancelled' => $orderStats->cancelled,
                'pending' => $orderStats->pending,
            ],
            'products' => [
                'active_approved' => $productStats->active_approved,
                'inactive' => $productStats->inactive,
                'pending' => $productStats->pending,
                'rejected' => $productStats->rejected,
            ],
            'wallet' => [
                'total_sales' => $totalSales,
            ],
            'last_five_orders' => $lastFiveDeliveredOrders,
            'sales_chart' => [
                'labels' => $labels,
                'data' => $data,
            ],
            'top_products' => $topSellingProducts,
        ]);
    }
}
