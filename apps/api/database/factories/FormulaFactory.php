<?php

namespace Database\Factories;

use App\Models\Farm;
use App\Models\Formula;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Formula> */
class FormulaFactory extends Factory
{
    protected $model = Formula::class;

    public function definition(): array
    {
        return [
            'farm_id' => Farm::factory(),
            'nom' => 'Formule '.fake()->unique()->word(),
            'phase' => 'croissance',
            'mode' => 'manual',
            'cost_per_kg' => fake()->numberBetween(200, 400),
        ];
    }
}
