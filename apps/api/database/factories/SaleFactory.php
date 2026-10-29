<?php

namespace Database\Factories;

use App\Models\Farm;
use App\Models\Sale;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Sale> */
class SaleFactory extends Factory
{
    protected $model = Sale::class;

    public function definition(): array
    {
        return [
            'farm_id' => Farm::factory(),
            'date_vente' => fake()->dateTimeBetween('-6 months')->format('Y-m-d'),
            'statut_paiement' => 'paye',
            'total' => 0,
        ];
    }
}
