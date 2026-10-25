<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Mortality\StoreMortalityRequest;
use App\Http\Resources\MortalityResource;
use App\Models\Batch;
use App\Models\MortalityRecord;
use App\Models\Pig;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;

class MortalityController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = MortalityRecord::query()
            ->when($request->filled('from'), fn ($q) => $q->whereDate('died_on', '>=', $request->date('from')))
            ->when($request->filled('to'), fn ($q) => $q->whereDate('died_on', '<=', $request->date('to')))
            ->when($request->filled('cause'), fn ($q) => $q->where('cause', $request->string('cause')))
            ->when($request->filled('batch_id'), fn ($q) => $q->where('batch_id', $request->integer('batch_id')));

        return MortalityResource::collection($query->latest('died_on')->paginate(20));
    }

    public function store(StoreMortalityRequest $request): JsonResponse
    {
        $data = $request->validated();

        $record = DB::transaction(function () use ($data) {
            // Individual death: derive the batch, force count 1, mark the pig dead.
            if (! empty($data['pig_id'])) {
                $pig = Pig::findOrFail($data['pig_id']);
                $data['batch_id'] = $pig->batch_id;
                $data['count'] = 1;
                $pig->update(['status' => 'dead']);
            }

            return MortalityRecord::create($data);
        });

        return MortalityResource::make($record)->response()->setStatusCode(201);
    }

    public function destroy(MortalityRecord $mortality): JsonResponse
    {
        $mortality->delete();

        return response()->json(['message' => 'Mortality record deleted.']);
    }

    // Total deaths + per-batch mortality rate (deaths / effectif_initial).
    public function stats(): JsonResponse
    {
        $total = (int) MortalityRecord::sum('count');

        $byBatch = Batch::withSum('mortalityRecords as deaths', 'count')->get()
            ->map(fn (Batch $b) => [
                'batch_id' => $b->id,
                'nom' => $b->nom,
                'effectif_initial' => $b->effectif_initial,
                'deaths' => (int) $b->deaths,
                'rate_pct' => $b->effectif_initial > 0
                    ? round((int) $b->deaths / $b->effectif_initial * 100, 1)
                    : null,
            ]);

        return response()->json(['data' => [
            'total_deaths' => $total,
            'by_batch' => $byBatch,
        ]]);
    }
}
