<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SpecificationsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $specifications = ['cpu', 'cpu_speed', 'cpu_generation', 'cpu_series', 
            'gpu_type', 'gpu_brand', 'gpu_model', 'gpu_memory',
            'ram_type', 'ram_capacity', 'ram_speed', 'ram_slots',
            'storage_type', 'storage_capacity', 'storage_speed',
            'display_size', 'display_resolution', 'display_refresh_rate',
            'battery_capacity', 'battery_life', 'material', 'dimensions',
            'ports', 'connectivity', 'audio', 'keyboard', 'touchpad',
            'camera', 'microphone', 'speakers',
            'os', 'weight', 'color', 'warranty',
        ];

        foreach ($specifications as $specification) {
            \DB::table('specifications')->updateOrInsert(
                ['name' => $specification],
                ['created_at' => now(), 'updated_at' => now()]
            );
        }
    }
}
