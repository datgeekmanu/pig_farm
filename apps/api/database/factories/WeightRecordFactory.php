<?php

namespace Database\Factories;

use App\Models\Farm;
use App\Models\Pig;
use App\Models\WeightRecord;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<WeightRecord> */
class WeightRecordFactory extends Factory
{
    protected $model = WeightRecord::class;

    public function definition(): array
    {
        return [
            'farm_id' => Farm::factory(),
            'pig_id' => Pig::factory(),
            'weight_kg' => fake()->randomFloat(2, 5, 120),
            'weighed_on' => fake()->dateTimeBetween('-3 months')->format('Y-m-d'),
        ];
    }
}
