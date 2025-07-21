<?php

namespace Database\Seeders;

use App\Models\MainCategory;
use App\Models\MainCategoryTranslation;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Faker\Generator;
use Faker\Provider\Image;

class MainCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = ['Labtops', 'PCs', 'Accessories', 'Cameras'];
        foreach($categories as $category){
            $mainCategory = MainCategory::create([
                'icon' => null,
            ]);

             $existing = MainCategoryTranslation::where('main_category_id', $mainCategory->id)
                ->where('locale', 'en')
                ->first();

            if (!$existing) {
                MainCategoryTranslation::create([
                    'main_category_id' => $mainCategory->id,
                    'locale' => 'en',
                    'name' => $category
                ]);
            }
        }
    }
}
