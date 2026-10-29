<?php

namespace Database\Factories;

use App\Models\Batch;
use App\Models\Farm;
use App\Models\FeedIntake;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<FeedIntake> */
class FeedIntakeFactory extends Factory
{
    protected $model = FeedIntake::class;

    public function definition(): array
    {
        return [
            'farm_id' => Farm::factory(),
            'batch_id' => Batch::factory(),
            'date' => fake()->dateTimeBetween('-2 months')->format('Y-m-d'),
            'quantity_kg' => fake()->randomFloat(2, 50, 500),
        ];
    }
}
