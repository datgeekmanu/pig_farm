<?php

namespace Database\Factories;

use App\Models\Farm;
use App\Models\Farrowing;
use App\Models\Pig;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Farrowing> */
class FarrowingFactory extends Factory
{
    protected $model = Farrowing::class;

    public function definition(): array
    {
        return [
            'farm_id' => Farm::factory(),
            'sow_id' => Pig::factory(),
            'farrowed_on' => fake()->dateTimeBetween('-2 months')->format('Y-m-d'),
            'born_alive' => fake()->numberBetween(8, 14),
            'born_dead' => fake()->numberBetween(0, 3),
        ];
    }
}
