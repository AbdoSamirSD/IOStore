<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class WalletSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $vendors = \App\Models\Vendor::all();
        foreach ($vendors as $vendor) {
            $vendor->wallet()->create([
                'vendor_id' => $vendor->id,
                'balance' => rand(1000, 5000),
                'pending_balance' => rand(100, 500),
                'total_earnings' => rand(5000, 10000),
                'total_withdrawn' => rand(1000, 3000),
                'total_refunded' => rand(100, 500),
                'total_commission' => rand(200, 800),
            ]);
        }
    }
}
