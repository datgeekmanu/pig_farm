<?php

namespace Database\Factories;

use App\Models\Farm;
use App\Models\MortalityRecord;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<MortalityRecord> */
class MortalityRecordFactory extends Factory
{
    protected $model = MortalityRecord::class;

    public function definition(): array
    {
        return [
            'farm_id' => Farm::factory(),
            'died_on' => fake()->dateTimeBetween('-3 months')->format('Y-m-d'),
            'cause' => fake()->randomElement(['maladie', 'écrasement', 'inconnue']),
            'count' => 1,
        ];
    }
}
