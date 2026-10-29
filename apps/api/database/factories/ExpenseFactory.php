<?php

namespace Database\Factories;

use App\Models\Expense;
use App\Models\Farm;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Expense> */
class ExpenseFactory extends Factory
{
    protected $model = Expense::class;

    public function definition(): array
    {
        return [
            'farm_id' => Farm::factory(),
            'date' => fake()->dateTimeBetween('-6 months')->format('Y-m-d'),
            'montant' => fake()->numberBetween(5000, 500000),
            'categorie' => fake()->randomElement(['Alimentation', 'Vétérinaire', 'Transport', 'Autre']),
        ];
    }
}
