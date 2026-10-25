<?php

namespace Database\Seeders;

use App\Models\Nutrient;
use App\Models\PhaseRequirement;
use Illuminate\Database\Seeder;

// Approximate nutrient targets per growth phase.
// ponytail: placeholder values — replace with official INRA/NRC tables (v2) or the
// farm's own targets before relying on them for real formulation.
class PhaseRequirementSeeder extends Seeder
{
    public function run(): void
    {
        $nutrients = Nutrient::pluck('id', 'code');

        // phase => [nutrient code => [min, max]] (null = unconstrained on that side)
        $targets = [
            'sevrage' => ['em' => [3300, null], 'mat' => [20, null], 'lysine' => [1.30, null], 'calcium' => [0.80, 1.00], 'phosphore' => [0.60, 0.80]],
            'demarrage' => ['em' => [3200, null], 'mat' => [18, null], 'lysine' => [1.10, null], 'calcium' => [0.75, 0.95], 'phosphore' => [0.55, 0.75]],
            'croissance' => ['em' => [3100, null], 'mat' => [16, null], 'lysine' => [0.90, null], 'calcium' => [0.70, 0.90], 'phosphore' => [0.50, 0.70]],
            'finition' => ['em' => [3100, null], 'mat' => [14, null], 'lysine' => [0.75, null], 'calcium' => [0.60, 0.80], 'phosphore' => [0.45, 0.65]],
        ];

        foreach ($targets as $phase => $reqs) {
            foreach ($reqs as $code => [$min, $max]) {
                PhaseRequirement::updateOrCreate(
                    ['phase' => $phase, 'nutrient_id' => $nutrients[$code]],
                    ['min_value' => $min, 'max_value' => $max],
                );
            }
        }
    }
}
