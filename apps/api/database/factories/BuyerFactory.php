<?php

namespace Database\Factories;

use App\Models\Buyer;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/** @extends Factory<Buyer> */
class BuyerFactory extends Factory
{
    protected $model = Buyer::class;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'password' => Hash::make('password'),
            'phone' => fake()->phoneNumber(),
        ];
    }
}
