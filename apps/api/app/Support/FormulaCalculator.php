<?php

namespace App\Support;

use App\Models\Ingredient;
use App\Models\PhaseRequirement;

class FormulaCalculator
{
    /**
     * @param  list<array{ingredient:Ingredient,percent:float}>  $lines
     * @return array<string,float> nutrient code => amount per kg of formula
     */
    public static function nutrition(array $lines): array
    {
        $totals = [];

        foreach ($lines as $line) {
            $frac = $line['percent'] / 100;
            foreach ($line['ingredient']->nutrientMap() as $code => $value) {
                $totals[$code] = ($totals[$code] ?? 0) + $frac * $value;
            }
        }

        return array_map(fn ($v) => round($v, 3), $totals);
    }

    /**
     * Cost per kg of formula, in minor currency units.
     *
     * @param  list<array{ingredient:Ingredient,percent:float}>  $lines
     */
    public static function costPerKg(array $lines): int
    {
        $cost = 0.0;
        foreach ($lines as $line) {
            $cost += ($line['percent'] / 100) * $line['ingredient']->prix_kg;
        }

        return (int) round($cost);
    }

    /**
     * Compare a nutrition profile to a phase's requirements.
     *
     * @param  array<string,float>  $nutrition
     * @return list<array<string,mixed>>
     */
    public static function analyze(string $phase, array $nutrition): array
    {
        return PhaseRequirement::with('nutrient')->where('phase', $phase)->get()
            ->map(function (PhaseRequirement $req) use ($nutrition) {
                $code = $req->nutrient->code;
                $value = $nutrition[$code] ?? 0;
                $min = $req->min_value !== null ? (float) $req->min_value : null;
                $max = $req->max_value !== null ? (float) $req->max_value : null;

                // 0.5% tolerance: least-cost mixes sit exactly on a binding limit,
                // so rounding shouldn't flip an on-target nutrient to low/high.
                $status = 'ok';
                if ($min !== null && $value < $min - abs($min) * 0.005) {
                    $status = 'low';
                } elseif ($max !== null && $value > $max + abs($max) * 0.005) {
                    $status = 'high';
                }

                return [
                    'nutrient' => $code,
                    'unit' => $req->nutrient->unit,
                    'value' => round($value, 3),
                    'min' => $min,
                    'max' => $max,
                    'status' => $status,
                ];
            })
            ->values()
            ->all();
    }
}
