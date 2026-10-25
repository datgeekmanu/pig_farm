<?php

namespace App\Support;

use App\Models\Ingredient;
use App\Models\PhaseRequirement;
use Illuminate\Support\Collection;

class FeedOptimizer
{
    /**
     * Least-cost formulation: find ingredient proportions that meet the phase's
     * nutrient targets at minimum cost.
     *
     * @param  Collection<int,Ingredient>  $ingredients  (nutrients eager-loaded)
     * @return array{status:string,lines:list<array{ingredient_id:int,percent:float}>,cost_per_kg:?int,nutrition:array<string,float>,analysis:list<array<string,mixed>>}
     */
    public static function optimize(Collection $ingredients, string $phase): array
    {
        $ingredients = $ingredients->values();

        if ($ingredients->isEmpty()) {
            return self::empty(Simplex::INFEASIBLE);
        }

        $maps = $ingredients->map(fn (Ingredient $i) => $i->nutrientMap());
        $cost = $ingredients->map(fn (Ingredient $i) => (float) $i->prix_kg)->all();

        // mass balance: proportions sum to 1
        $constraints = [[
            'coeffs' => array_fill(0, $ingredients->count(), 1.0),
            'op' => '=',
            'rhs' => 1.0,
        ]];

        foreach (PhaseRequirement::with('nutrient')->where('phase', $phase)->get() as $req) {
            $code = $req->nutrient->code;
            $col = $maps->map(fn (array $m) => (float) ($m[$code] ?? 0))->all();

            if ($req->min_value !== null) {
                $constraints[] = ['coeffs' => $col, 'op' => '>=', 'rhs' => (float) $req->min_value];
            }
            if ($req->max_value !== null) {
                $constraints[] = ['coeffs' => $col, 'op' => '<=', 'rhs' => (float) $req->max_value];
            }
        }

        $result = Simplex::minimize($cost, $constraints);

        if ($result['status'] !== Simplex::OPTIMAL) {
            return self::empty($result['status']);
        }

        $lines = [];
        $calcLines = [];
        foreach ($result['x'] as $i => $fraction) {
            $percent = round($fraction * 100, 2);
            if ($percent <= 0.0) {
                continue;
            }
            $ingredient = $ingredients[$i];
            $lines[] = ['ingredient_id' => $ingredient->id, 'percent' => $percent];
            $calcLines[] = ['ingredient' => $ingredient, 'percent' => $percent];
        }

        $nutrition = FormulaCalculator::nutrition($calcLines);

        return [
            'status' => Simplex::OPTIMAL,
            'lines' => $lines,
            'cost_per_kg' => FormulaCalculator::costPerKg($calcLines),
            'nutrition' => $nutrition,
            'analysis' => FormulaCalculator::analyze($phase, $nutrition),
        ];
    }

    /**
     * @return array{status:string,lines:array,cost_per_kg:null,nutrition:array,analysis:array}
     */
    private static function empty(string $status): array
    {
        return ['status' => $status, 'lines' => [], 'cost_per_kg' => null, 'nutrition' => [], 'analysis' => []];
    }
}
