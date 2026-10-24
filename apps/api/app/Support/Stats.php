<?php

namespace App\Support;

class Stats
{
    /**
     * Summary stats for a herd weight sample, incl. homogénéité as CV%
     * (coefficient of variation = population stddev / mean × 100).
     *
     * @param  list<float>  $values
     * @return array{count:int,mean:?float,std:?float,cv_pct:?float,min:?float,max:?float}
     */
    public static function summary(array $values): array
    {
        $count = count($values);

        if ($count === 0) {
            return ['count' => 0, 'mean' => null, 'std' => null, 'cv_pct' => null, 'min' => null, 'max' => null];
        }

        $mean = array_sum($values) / $count;
        $variance = array_sum(array_map(fn ($v) => ($v - $mean) ** 2, $values)) / $count;
        $std = sqrt($variance);

        return [
            'count' => $count,
            'mean' => round($mean, 2),
            'std' => round($std, 2),
            'cv_pct' => $mean > 0 ? round($std / $mean * 100, 1) : null,
            'min' => round(min($values), 2),
            'max' => round(max($values), 2),
        ];
    }
}
