<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\CommissionPlan;
use App\Models\CommissionRange;

class CommissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $vendorId = 4; // Assuming you want to set the commission for vendor with ID 1
        $categoryId = 3; // Assuming you want to set the commission for category with ID 1
        
        $plan = CommissionPlan::create([
            'vendor_id' => $vendorId,
            'product_category_id' => $categoryId,
            'plan_name' => 'Default Commission Plan', 
            'commission_type' => 'variable', // or 'fixed'
        ]);

        $range = [
            ['min_value' => 0, 'max_value' => 100, 'percentage' => 10,],
            ['min_value' => 101, 'max_value' => 500, 'percentage' => 15,],
            ['min_value' => 501, 'max_value' => 1000, 'percentage' => 20,],
        ];

        foreach ($range as $r) {
            CommissionRange::create([
                'commission_plan_id' => $plan->id,
                'min_value' => $r['min_value'],
                'max_value' => $r['max_value'],
                'percentage' => $r['percentage'],
            ]);
        }
    }
}
