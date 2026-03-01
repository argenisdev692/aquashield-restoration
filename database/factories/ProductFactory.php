<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\CategoryProduct;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'uuid' => (string) Str::uuid(),
            'product_category_id' => CategoryProduct::factory(),
            'product_name' => $this->faker->words(3, true),
            'product_description' => $this->faker->sentence(10),
            'price' => $this->faker->randomFloat(2, 10, 1000),
            'unit' => $this->faker->randomElement(['unit', 'box', 'pack', 'piece', 'set']),
            'order_position' => $this->faker->numberBetween(1, 100),
        ];
    }
}
