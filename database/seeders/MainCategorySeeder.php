<?php

namespace Database\Seeders;

use App\Models\MainCategory;
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
        $categories = [
            'عناية شخصية' => 'Personal Care',
            'سكين كير' => 'Skin Care',
            'بيبي كير' => 'Baby Care',
        ];
        foreach ($categories as $catAR => $catEN) {
            MainCategory::create([
                'ar' =>
                    ['name' => $catAR],
                'en' => ['name' => $catEN],
            ]);
        }
    }
}
