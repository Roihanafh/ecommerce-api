<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    public function definition(): array
    {
        $name = $this->faker->unique()->words(3, true);

        return [
            'category_id' => Category::factory(),
            'name'        => $name,
            'slug'        => Str::slug($name),
            'description' => $this->faker->optional()->paragraph(),
            'price'       => $this->faker->randomFloat(2, 10000, 50000000),
            'stock'       => $this->faker->numberBetween(0, 500),
            'image'       => null,
            'is_active'   => $this->faker->boolean(80),
        ];
    }
}
