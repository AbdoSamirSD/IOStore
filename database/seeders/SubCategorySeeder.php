<?php

namespace Database\Seeders;

use App\Models\MainCategory;
use App\Models\SubCategory;
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
            'عناية شخصية' => [
                'صوفي',
                'فينوس',
                'الويز',
                'برايفت',
                'حرير',
                'مولبيد',
                'دراكون',
                'ازهي',
                'ستار فيل',
                'نيفيا',
                'ايفا'
            ],
            'سكين كير' => [
                'الوكيتا',
                'ستار فيل',
                'اليجون',
                'ديزار',
                'انفينتي',
                'ارجنتو',
                'بيزلين',
                'تريزيمي',
                'جليسوليد',
                'كير اند مور',
                'لونا',
                'نيفيا',
                'كولاجرا',
                'غارنية',
                'شان',
                'بوباي'
            ],
            'بيبي كير' => [
                'بيندولين',
                'بابلز',
                'ابيكس',
                'الجو',
                'لاكي بيبي',
                'نايس بيبي',
                'فلورو',
                'نونو'
            ],
        ];
        foreach ($subCategories as $mainCategoryAr => $subcatList) {
            $mainCategory = MainCategory::whereTranslationLike('name', $mainCategoryAr)->first();

            foreach ($subcatList as $subcatAr) {
                $mainCategory->subCategories()->create([
                    'ar' => ['name' => $subcatAr],
                    'en' => ['name' => $subcatAr],
                ]);
            }
        }
    }
}
