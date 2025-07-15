<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    // 1. Total Orders and Revenue
    public function totalOrdersAndRevenue()
    {
        $totalOrders = Order::count();
        $totalRevenue = Order::sum('total_cost');

        return [
            'total_orders' => $totalOrders,
            'total_revenue' => $totalRevenue,
        ];
    }

    // 2. Order Status Breakdown
    public function orderStatusBreakdown()
    {
        $statuses = Order::select('status', \DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get();

        return $statuses;
    }
    // 4. Discount Usage
    public function discountUsage()
    {
        $ordersWithDiscount = Order::where('discount', '>', 0)->count();
        $totalDiscount = Order::sum('discount');

        return [
            'orders_with_discount' => $ordersWithDiscount,
            'total_discount' => $totalDiscount,
        ];
    }
    // 3. Top-Selling Products
    public function topSellingProducts()
    {
        $topProducts = Product::withCount(['orders as order_count'])
            ->orderBy('order_count', 'desc')
            ->take(10)

            ->get();

        return $topProducts;
    }

    public function index()
    {
        return response()->json([
            'totalOrdersAndRevenue' => $this->totalOrdersAndRevenue(),
            'orderStatusBreakdown' => $this->orderStatusBreakdown(),
            // 'topSellingProducts' => $this->topSellingProducts(),
            // 'discountUsage' => $this->discountUsage(),
        ]);
    }
}
