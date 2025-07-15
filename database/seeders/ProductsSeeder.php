<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        for ($i = 0; $i < 10; $i++) {
            \App\Models\Product::create(
                [
                    'main_category_id' => rand(1, 4),
                    'sub_category_id' => rand(1, 4),
                    'price' => rand(1000, 5000),
                    'supplier_price' => rand(1000, 5000),
                    'stock' => rand(1, 100),
                    'discount' => rand(1, 100),
                    'colors' => json_encode([0x000000, 0xffffff]),
                    'en' => [
                        'name' => 'product ' . $i,
                        'description' => 'product ' . $i . ' description',
                        'details' => 'product ' . $i . ' details',
                        'instructions' => json_encode(['product ' . $i . ' instruction 1', 'product ' . $i . ' instruction 2',])
                        ,
                    ],
                    'ar' => [
                        'name' => 'منتج ' . $i,
                        'description' => 'منتج ' . $i . ' description',
                        'details' => 'منتج ' . $i . ' details',
                        'instructions' => json_encode(['منتج ' . $i . ' instruction 1', 'منتج ' . $i . ' instruction 2',])
                    ],
                ]
            );
        }
    }
}
