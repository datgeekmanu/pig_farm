<?php

namespace Database\Factories;

use App\Models\Farm;
use App\Models\Pig;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Pig> */
class PigFactory extends Factory
{
    protected $model = Pig::class;

    public function definition(): array
    {
        return [
            'farm_id' => Farm::factory(),
            'identifiant' => 'P'.fake()->unique()->numerify('####'),
            'sexe' => fake()->randomElement(['male', 'female']),
            'categorie' => fake()->randomElement(['verrat', 'truie', 'engraissement', 'porcelet']),
            'poids_arrivee_kg' => fake()->randomFloat(2, 15, 40),
            'status' => 'active',
        ];
    }
}
