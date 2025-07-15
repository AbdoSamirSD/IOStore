<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Banner;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Banner>
 */
class BannerFactory extends Factory
{

    protected $model = Banner::class;

    public function definition()
    {
        return [
            'title' => $this->faker->sentence,
            'image_path' => 'uploads/banners/' . $this->faker->image('public/uploads/banners', 640, 480, null, false),
            'link' => $this->faker->url,
            'product_id' => Product::inRandomOrder()->first()->id,
            'type' => $this->faker->randomElement(['offer', 'new_product']),
        ];

    }
}
