<?php

namespace Database\Factories;

use App\Models\Farm;
use App\Models\Salary;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Salary> */
class SalaryFactory extends Factory
{
    protected $model = Salary::class;

    public function definition(): array
    {
        return [
            'farm_id' => Farm::factory(),
            'nom_ouvrier' => fake()->name(),
            'montant' => fake()->numberBetween(30000, 150000),
            'date_paiement' => fake()->dateTimeBetween('-3 months')->format('Y-m-d'),
            'mois_concerne' => fake()->dateTimeBetween('-3 months')->format('Y-m'),
        ];
    }
}
