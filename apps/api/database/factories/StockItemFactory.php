<?php

namespace Database\Factories;

use App\Models\Farm;
use App\Models\StockItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<StockItem> */
class StockItemFactory extends Factory
{
    protected $model = StockItem::class;

    public function definition(): array
    {
        return [
            'farm_id' => Farm::factory(),
            'type' => fake()->randomElement(['alimentation', 'veterinaire']),
            'nom' => fake()->unique()->word(),
            'quantite' => fake()->randomFloat(2, 0, 500),
            'unite' => 'kg',
        ];
    }
}
