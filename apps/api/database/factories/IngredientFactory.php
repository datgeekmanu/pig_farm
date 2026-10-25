<?php

namespace Database\Factories;

use App\Models\Farm;
use App\Models\Ingredient;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Ingredient> */
class IngredientFactory extends Factory
{
    protected $model = Ingredient::class;

    public function definition(): array
    {
        return [
            'farm_id' => Farm::factory(),
            'nom' => fake()->unique()->word(),
            'prix_kg' => fake()->numberBetween(100, 800),
        ];
    }
}
