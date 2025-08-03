<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Models\Order;

class WalletTransactionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = ['order_earning', 'order_refund', 'order_cancellation', 'order_chargeback', 'order_payment', 'withdraw'];
        $directions = ['credit', 'debit'];
        $statuses = ['pending', 'approved', 'rejected'];
        $orders = Order::pluck('id')->toArray();

        Wallet::all()->each(function ($wallet) use ($types, $directions, $statuses, $orders) {
            for ($i = 0; $i < rand(5, 10); $i++) {
                WalletTransaction::create([
                    'wallet_id' => $wallet->id,
                    'type' => $types[array_rand($types)],
                    'amount' => rand(100, 1000),
                    'description' => "Sample transaction of type {$types[array_rand($types)]}",
                    'direction' => $directions[array_rand($directions)],
                    'related_order_id' => count($orders) > 0 ? $orders[array_rand($orders)] : null,
                    'status' => $statuses[array_rand($statuses)],
                ]);
            }
        });
    }
}
