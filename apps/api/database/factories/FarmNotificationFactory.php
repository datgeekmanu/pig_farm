<?php

namespace Database\Factories;

use App\Models\Farm;
use App\Models\FarmNotification;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<FarmNotification> */
class FarmNotificationFactory extends Factory
{
    protected $model = FarmNotification::class;

    public function definition(): array
    {
        return [
            'farm_id' => Farm::factory(),
            'type' => 'stock_bas',
            'title' => 'Alerte',
            'dedupe_key' => 'test:'.fake()->unique()->numberBetween(1, 999999),
        ];
    }
}
