<?php

namespace Database\Factories;

use App\Models\Batch;
use App\Models\Farm;
use App\Models\LotWeighing;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<LotWeighing> */
class LotWeighingFactory extends Factory
{
    protected $model = LotWeighing::class;

    public function definition(): array
    {
        return [
            'farm_id' => Farm::factory(),
            'batch_id' => Batch::factory(),
            'weighed_on' => fake()->dateTimeBetween('-2 months')->format('Y-m-d'),
            'avg_weight_kg' => fake()->randomFloat(2, 20, 100),
            'count' => fake()->numberBetween(5, 30),
        ];
    }
}
