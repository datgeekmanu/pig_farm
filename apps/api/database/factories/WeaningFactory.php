<?php

namespace Database\Factories;

use App\Models\Farm;
use App\Models\Farrowing;
use App\Models\Weaning;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Weaning> */
class WeaningFactory extends Factory
{
    protected $model = Weaning::class;

    public function definition(): array
    {
        return [
            'farm_id' => Farm::factory(),
            'farrowing_id' => Farrowing::factory(),
            'weaned_on' => fake()->dateTimeBetween('-1 month')->format('Y-m-d'),
            'weaned_count' => fake()->numberBetween(6, 12),
        ];
    }
}
