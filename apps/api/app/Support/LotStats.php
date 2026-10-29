<?php

namespace App\Support;

use App\Models\Batch;

// Aggregate lot metrics for the field/dashboard apps: current head, average
// weight + uniformity, ADG (GMQ), feed conversion (IC), phase, age.
class LotStats
{
    /**
     * @return array<string, mixed>
     */
    public static function for(Batch $batch): array
    {
        $head = $batch->currentHead();

        $weighings = $batch->lotWeighings()->orderBy('weighed_on')->get();
        $latest = $weighings->last();
        $first = $weighings->first();

        $initial = $batch->poids_moyen_initial_kg !== null
            ? (float) $batch->poids_moyen_initial_kg
            : ($first ? (float) $first->avg_weight_kg : null);
        $avg = $latest ? (float) $latest->avg_weight_kg : $initial;
        $cv = $latest && $latest->uniformity_cv !== null ? (float) $latest->uniformity_cv : null;

        // ADG (g/day) between mise-en-lot and the latest weigh-in.
        $adg = null;
        if ($latest && $initial !== null) {
            $days = abs($batch->date_mise_en_lot->diffInDays($latest->weighed_on));
            if ($days > 0) {
                $adg = (int) round(($avg - $initial) / $days * 1000);
            }
        }

        // FCR = total feed consumed / total live-weight gain.
        $fcr = null;
        if ($latest && $initial !== null && $head > 0) {
            $feedKg = (float) $batch->feedIntakes()->sum('quantity_kg');
            $totalGain = ($avg - $initial) * $head;
            if ($feedKg > 0 && $totalGain > 0) {
                $fcr = round($feedKg / $totalGain, 2);
            }
        }

        $ageFrom = $batch->date_naissance ?? $batch->date_mise_en_lot;

        return [
            'headcount' => $head,
            'avg_weight_kg' => $avg !== null ? round($avg, 1) : null,
            'uniformity_cv' => $cv,
            'adg_g_per_day' => $adg,
            'feed_conversion' => $fcr,
            'phase' => $avg !== null ? self::phaseForWeight($avg) : null,
            'age_days' => $ageFrom ? (int) abs($ageFrom->diffInDays(now())) : null,
        ];
    }

    public static function phaseForWeight(float $kg): string
    {
        foreach (config('growth.weight_phases') as $p) {
            if ($p['max'] === null || $kg < $p['max']) {
                return $p['label'];
            }
        }

        return 'Finition';
    }
}
