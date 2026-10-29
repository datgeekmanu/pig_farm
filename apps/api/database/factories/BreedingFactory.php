<?php

namespace Database\Factories;

use App\Models\Breeding;
use App\Models\Farm;
use App\Models\Pig;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/** @extends Factory<Breeding> */
class BreedingFactory extends Factory
{
    protected $model = Breeding::class;

    public function definition(): array
    {
        $bredOn = fake()->dateTimeBetween('-5 months', '-1 month');

        return [
            'farm_id' => Farm::factory(),
            'sow_id' => Pig::factory(),
            'bred_on' => $bredOn->format('Y-m-d'),
            'expected_farrow_on' => Carbon::parse($bredOn)->addDays(114)->toDateString(),
            'status' => 'saillie',
        ];
    }
}
