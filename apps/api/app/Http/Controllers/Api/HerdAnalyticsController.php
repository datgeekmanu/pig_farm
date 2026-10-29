<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Batch;
use App\Models\Pig;
use App\Models\WeightRecord;
use App\Support\GrowthCurve;
use App\Support\Stats;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;

class HerdAnalyticsController extends Controller
{
    // Poids moyen + homogénéité (CV%) from the latest weight of each pig in the batch.
    public function batchWeightStats(Batch $batch): JsonResponse
    {
        $latestPerPig = WeightRecord::where('batch_id', $batch->id)
            ->orderByDesc('weighed_on')
            ->orderByDesc('id')
            ->get()
            ->groupBy('pig_id')
            ->map(fn ($records) => (float) $records->first()->weight_kg)
            ->values()
            ->all();

        return response()->json(['data' => Stats::summary($latestPerPig)]);
    }

    // Reference envelope vs the batch's actual average weight per age-week.
    public function batchGrowthCurve(Batch $batch): JsonResponse
    {
        $records = WeightRecord::where('batch_id', $batch->id)->with('pig')->get();

        $actual = $records
            ->map(function (WeightRecord $r) {
                $week = self::ageWeek($r->pig, $r->weighed_on);

                return $week === null ? null : ['week' => $week, 'weight' => (float) $r->weight_kg];
            })
            ->filter()
            ->groupBy('week')
            ->map(fn ($group, $week) => [
                'week' => (int) $week,
                'weight_kg' => round($group->avg('weight'), 2),
                'count' => $group->count(),
            ])
            ->sortBy('week')
            ->values();

        return $this->curveResponse($actual->all());
    }

    public function pigGrowthCurve(Pig $pig): JsonResponse
    {
        $actual = $pig->weights()
            ->orderBy('weighed_on')
            ->get()
            ->map(function (WeightRecord $r) use ($pig) {
                $week = self::ageWeek($pig, $r->weighed_on);

                return $week === null ? null : ['week' => $week, 'weight_kg' => (float) $r->weight_kg];
            })
            ->filter()
            ->values();

        return $this->curveResponse($actual->all());
    }

    /**
     * @param  array<int, array<string, mixed>>  $actual
     */
    private function curveResponse(array $actual): JsonResponse
    {
        return response()->json([
            'reference' => GrowthCurve::reference(),
            'phases' => GrowthCurve::phases(),
            'actual' => $actual,
        ]);
    }

    // Age in whole weeks at weigh-in, or null when the pig has no age reference date.
    private static function ageWeek(Pig $pig, Carbon $weighedOn): ?int
    {
        $base = $pig->ageReferenceDate();

        if (! $base) {
            return null;
        }

        return (int) floor(abs($base->diffInDays($weighedOn)) / 7);
    }
}
