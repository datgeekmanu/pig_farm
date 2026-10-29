<?php

namespace Database\Factories;

use App\Models\Building;
use App\Models\Farm;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Building> */
class BuildingFactory extends Factory
{
    protected $model = Building::class;

    public function definition(): array
    {
        return [
            'farm_id' => Farm::factory(),
            'nom' => 'Bâtiment '.fake()->unique()->numberBetween(1, 999),
            'surface_m2' => fake()->randomFloat(2, 50, 300),
        ];
    }
}
