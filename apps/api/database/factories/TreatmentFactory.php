<?php

namespace Database\Factories;

use App\Models\Farm;
use App\Models\Treatment;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Treatment> */
class TreatmentFactory extends Factory
{
    protected $model = Treatment::class;

    public function definition(): array
    {
        return [
            'farm_id' => Farm::factory(),
            'scope' => 'lot',
            'date' => fake()->dateTimeBetween('-2 months')->format('Y-m-d'),
            'produit' => fake()->randomElement(['Ivermectine', 'Amoxicilline', 'Vaccin PPC']),
        ];
    }
}
