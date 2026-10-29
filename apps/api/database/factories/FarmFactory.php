<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\Farm;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Farm> */
class FarmFactory extends Factory
{
    protected $model = Farm::class;

    public function definition(): array
    {
        return [
            'account_id' => Account::factory(),
            'name' => 'Ferme '.fake()->lastName(),
            'currency' => 'XOF',
        ];
    }
}
