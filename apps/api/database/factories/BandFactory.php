<?php

namespace Database\Factories;

use App\Models\Band;
use App\Models\Farm;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Band> */
class BandFactory extends Factory
{
    protected $model = Band::class;

    public function definition(): array
    {
        return [
            'farm_id' => Farm::factory(),
            'nom' => 'Bande '.fake()->unique()->numberBetween(1, 999),
        ];
    }
}
