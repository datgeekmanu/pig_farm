<?php

namespace Database\Seeders;

use App\Models\Nutrient;
use Illuminate\Database\Seeder;

class NutrientSeeder extends Seeder
{
    public function run(): void
    {
        $nutrients = [
            ['code' => 'em', 'nom' => 'Énergie métabolisable', 'unit' => 'kcal/kg'],
            ['code' => 'mat', 'nom' => 'Protéines (MAT)', 'unit' => '%'],
            ['code' => 'lysine', 'nom' => 'Lysine', 'unit' => '%'],
            ['code' => 'calcium', 'nom' => 'Calcium', 'unit' => '%'],
            ['code' => 'phosphore', 'nom' => 'Phosphore', 'unit' => '%'],
            ['code' => 'cellulose', 'nom' => 'Cellulose brute', 'unit' => '%'],
        ];

        foreach ($nutrients as $n) {
            Nutrient::updateOrCreate(['code' => $n['code']], $n);
        }
    }
}
