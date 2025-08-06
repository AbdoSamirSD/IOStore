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
                COUNT(CASE WHEN status = 'preparing' THEN 1 END) as preparing,
                COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending
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

        $totalSales = Wallet::where('vendor_id', $vendor->id)->value('total_earnings') ?? 0;

        $lastFiveDeliveredOrders = Order::where('vendor_id', $vendor->id)
            ->where('status', 'delivered')
            ->latest()
            ->take(5)
            ->with(['items:id,order_id,name,image']) 
            ->get(['id', 'vendor_id', 'created_at']);

        $monthsBack = 6;
        $salesByMonth = Order::selectRaw("
                MONTH(created_at) as month_number,
                DATE_FORMAT(created_at, '%M') as month, 
                SUM(total_cost) as total,
                MIN(created_at) as first_order_date"
            )
            ->where('vendor_id', $vendor->id)
            ->where('status', 'delivered')
            ->where('created_at', '>=', now()->subMonths(value: $monthsBack))
            ->groupByRaw("MONTH(created_at), DATE_FORMAT(created_at, '%M')")
            ->orderByRaw('MIN(created_at) asc')
            ->get();

        $labels = $salesByMonth->pluck('month');
        $data = $salesByMonth->pluck('total');

        $topSellingProducts = OrderItem::selectRaw("
                product_id,
                SUM(quantity) as total_quantity,
                name,
                image
            ")
            ->whereHas('order', function ($query) use ($vendor) {
                $query//->where('status', 'delivered')
                    ->where('vendor_id', $vendor->id);
            })
            ->groupBy('product_id', 'name', 'image')
            ->orderBy('total_quantity', 'desc')
            ->take(5)
            ->get();

        return response()->json([
            'orders' => [
                'delivered' => $orderStats->delivered,
                'cancelled' => $orderStats->cancelled,
                'preparing' => $orderStats->preparing,
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
