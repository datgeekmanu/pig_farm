<?php

namespace Database\Factories;

use App\Models\Farm;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Product> */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'farm_id' => Farm::factory(),
            'type' => 'live_pig',
            'nom' => fake()->words(2, true),
            'unit' => 'tete',
            'prix' => fake()->numberBetween(50000, 200000),
            'stock_qty' => fake()->numberBetween(1, 50),
            'is_published' => true,
        ];
    }
}
