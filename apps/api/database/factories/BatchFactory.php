<?php

namespace Database\Factories;

use App\Models\Batch;
use App\Models\Farm;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Batch> */
class BatchFactory extends Factory
{
    protected $model = Batch::class;

    public function definition(): array
    {
        return [
            'farm_id' => Farm::factory(),
            'nom' => 'Lot '.fake()->unique()->numerify('####-?'),
            'batiment_loge' => 'Bâtiment '.fake()->numberBetween(1, 5),
            'date_mise_en_lot' => fake()->dateTimeBetween('-6 months')->format('Y-m-d'),
            'effectif_initial' => fake()->numberBetween(10, 50),
            'poids_moyen_initial_kg' => fake()->randomFloat(2, 20, 30),
            'status' => 'active',
        ];
    }
}
