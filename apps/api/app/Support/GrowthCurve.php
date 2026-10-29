<?php

namespace App\Support;

class GrowthCurve
{
    /**
     * Reference envelope per week: [['week','min','rec','max'], ...].
     *
     * @return list<array{week:int,min:float,rec:float,max:float}>
     */
    public static function reference(): array
    {
        $band = (float) config('growth.band_pct');

        return collect(config('growth.recommended_kg'))
            ->map(fn (float $rec, int $week) => [
                'week' => $week,
                'rec' => round($rec, 1),
                'min' => round($rec * (1 - $band), 1),
                'max' => round($rec * (1 + $band), 1),
            ])
            ->values()
            ->all();
    }

    /**
     * @return array<string, array{0:int,1:int}>
     */
    public static function phases(): array
    {
        return config('growth.phases');
    }
}
