<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Lot\StoreFeedIntakeRequest;
use App\Http\Requests\Lot\StoreLotWeighingRequest;
use App\Models\Batch;
use App\Models\StockItem;
use App\Support\Stats;
use App\Support\StockLedger;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use RuntimeException;

// Lot-level operations for the field app (aggregate, no per-pig entry).
class LotController extends Controller
{
    public function weighings(Batch $batch): JsonResponse
    {
        return response()->json(['data' => $batch->lotWeighings()->orderBy('weighed_on')->get()]);
    }

    // Unified lot timeline (weigh-ins, deaths, treatments, sales), newest first.
    public function events(Batch $batch): JsonResponse
    {
        $events = collect();

        foreach ($batch->lotWeighings as $w) {
            $events->push(['icon' => 'weight-scale', 'date' => $w->weighed_on->toDateString(),
                'text' => "Pesée : {$w->avg_weight_kg} kg de moyenne".($w->count ? " ({$w->count} animaux)" : '')]);
        }
        foreach ($batch->mortalityRecords as $m) {
            $events->push(['icon' => 'skull-crossbones', 'date' => $m->died_on->toDateString(),
                'text' => "{$m->count} mort(s)".($m->cause ? " · {$m->cause}" : '')]);
        }
        foreach ($batch->treatments as $t) {
            $events->push(['icon' => 'syringe', 'date' => $t->date->toDateString(),
                'text' => $t->produit.($t->dose ? " · {$t->dose}" : '')]);
        }
        foreach ($batch->saleItems()->with('sale')->get() as $i) {
            $events->push(['icon' => 'sack-dollar', 'date' => $i->sale?->date_vente?->toDateString(),
                'text' => "{$i->quantite} animaux vendus"]);
        }

        return response()->json(['data' => $events->sortByDesc('date')->values()]);
    }

    public function storeWeighing(StoreLotWeighingRequest $batchRequest, Batch $batch): JsonResponse
    {
        $data = $batchRequest->validated();

        if (! empty($data['weights'])) {
            $summary = Stats::summary(array_map('floatval', $data['weights']));
            [$avg, $cv, $count] = [$summary['mean'], $summary['cv_pct'], $summary['count']];
        } elseif (isset($data['avg_weight_kg'])) {
            [$avg, $cv, $count] = [round((float) $data['avg_weight_kg'], 2), null, $data['count'] ?? null];
        } else {
            [$avg, $cv, $count] = [round($data['total_kg'] / $data['count'], 2), null, $data['count']];
        }

        $weighing = $batch->lotWeighings()->create([
            'weighed_on' => $data['weighed_on'] ?? now()->toDateString(),
            'count' => $count,
            'avg_weight_kg' => $avg,
            'uniformity_cv' => $cv,
            'weights' => $data['weights'] ?? null,
            'notes' => $data['notes'] ?? null,
        ]);

        return response()->json(['data' => $weighing], 201);
    }

    public function feedIntakes(Batch $batch): JsonResponse
    {
        return response()->json(['data' => $batch->feedIntakes()->latest('date')->get()]);
    }

    public function storeFeed(StoreFeedIntakeRequest $request, Batch $batch): JsonResponse
    {
        $data = $request->validated();

        try {
            $intake = DB::transaction(function () use ($batch, $data) {
                $intake = $batch->feedIntakes()->create($data);

                if (! empty($data['stock_item_id'])) {
                    $item = StockItem::findOrFail($data['stock_item_id']);
                    StockLedger::apply($item, -(float) $data['quantity_kg'], 'alimentation');
                }

                return $intake;
            });
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json(['data' => $intake], 201);
    }
}
