<?php

namespace Database\Factories;

use App\Models\Brand;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'title' => fake()->sentence(rand(1,6)),
            'price' => fake()->randomFloat(2,2, 3),
            'description' => fake()->text(),
            'category_uuid' => Category::factory()->create()->uuid,
            'metadata' => [
                'brand' => Brand::factory()->create()->uuid
            ],
        ];
    }
}
