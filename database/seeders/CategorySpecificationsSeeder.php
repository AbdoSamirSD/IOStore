<?php

namespace Database\Seeders;

use App\Models\CategorySpecification;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\MainCategory;
use App\Models\Specification;

class CategorySpecificationsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $laptopSpecs = [
            'cpu', 'cpu_speed', 'cpu_generation', 'cpu_series',
            'gpu_type', 'gpu_brand', 'gpu_model', 'gpu_memory',
            'ram_type', 'ram_capacity', 'ram_speed', 'ram_slots',
            'storage_type', 'storage_capacity', 'storage_speed',
            'display_size', 'display_resolution', 'display_refresh_rate',
            'battery_capacity', 'battery_life', 'material', 'dimensions',
            'ports', 'connectivity', 'audio', 'keyboard', 'touchpad',
            'camera', 'microphone', 'speakers',
            'os', 'weight', 'color', 'warranty',
        ];

        $mainCategory = MainCategory::whereHas('translations', function ($query) {
            $query->where('name', 'Labtops');
        })->first();
        

        if (!$mainCategory) {
            $this->command->error('Main category "Laptops" not found.');
            return;
        }

        foreach ($laptopSpecs as $spec) {
            $specification = Specification::where('name', $spec)->first();
            
            if (!$specification){
                $this->command->warn("Specification '{$spec}' not found. Skipping.");
                continue;
            }

            CategorySpecification::create([
                'category_id' => $mainCategory->id,
                'specification_id' => $specification->id,
            ]);

            $this->command->info("Specification '{$spec}' added to category 'Laptops'.");
        }
    }
}
