<?php

namespace Database\Seeders;

use App\Models\MainCategory;
use App\Models\SubCategory;
use App\Models\SubCategoryTranslation;
use App\Models\MainCategoryTranslation;
use Illuminate\Support\Facades\DB;
use Faker\Generator;
use Faker\Provider\Image;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SubCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $subCategories = [
            'Laptops' => ['HP', 'Dell', 'Lenovo', 'Apple', 'Asus', 'Acer'],
            'PCs' => ['HP', 'Dell', 'Lenovo', 'Apple', 'Asus', 'Acer'],
            'Accessories' => [
                'Keyboards',
                'Mice',
                'Monitors',
                'Headphones',
                'Webcams',
                'Speakers',
                'Cables',
                'Chargers',
                'External Hard Drives',
                'USB Hubs',
                'Docking Stations',
                'Microphones',
                'Laptop Stands',
                'Mouse Pads',
                'bags',
                'Adapters',
                'Cooling Pads',
                'Screen Protectors',
                'Flash Drives',
            ],
            'Cameras' => [
                'DSLR',
                'Mirrorless',
                'Point and Shoot',
                'Action Cameras',
                'Camcorders',
                '360 Cameras',
                'Camera Lenses',
                'Tripods',
                'Gimbals',
                'Camera Bags',
                'Memory Cards',
                'Camera Accessories',
            ],
        ];

        foreach ($subCategories as $mainCategoryName => $subs) {
            $mainCategoryTranslation = MainCategoryTranslation::where('name', $mainCategoryName)
                ->where('locale', 'en')
                ->first();

            if (!$mainCategoryTranslation) continue;

            foreach ($subs as $subName) {
                $existing = SubCategoryTranslation::where('name', $subName)
                    ->where('locale', 'en')
                    ->first();

                if (!$existing) {
                    $subCategory = SubCategory::create([
                        'main_category_id' => $mainCategoryTranslation->main_category_id,
                        'icon' => null,
                    ]);

                    SubCategoryTranslation::create([
                        'sub_category_id' => $subCategory->id,
                        'locale' => 'en',
                        'name' => $subName,
                    ]);
                }
            }
        }
    }
}
