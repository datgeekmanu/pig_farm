<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SyncRequest;
use App\Models\MortalityRecord;
use App\Models\Pig;
use App\Models\WeightRecord;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

// Offline field-entry sync. Idempotent: each record carries a client_uuid, so
// replaying the same batch creates nothing new (conflict rule = dedupe by uuid).
class SyncController extends Controller
{
    public function sync(SyncRequest $request): JsonResponse
    {
        $data = $request->validated();
        $result = [
            'weights' => ['created' => 0, 'duplicates' => 0],
            'mortalities' => ['created' => 0, 'duplicates' => 0],
        ];

        DB::transaction(function () use ($data, &$result) {
            foreach ($data['weights'] ?? [] as $w) {
                if (WeightRecord::where('client_uuid', $w['client_uuid'])->exists()) {
                    $result['weights']['duplicates']++;

                    continue;
                }
                $pig = Pig::find($w['pig_id']);
                WeightRecord::create([
                    'client_uuid' => $w['client_uuid'],
                    'pig_id' => $pig->id,
                    'batch_id' => $pig->batch_id,
                    'weight_kg' => $w['weight_kg'],
                    'weighed_on' => $w['weighed_on'] ?? now()->toDateString(),
                    'notes' => $w['notes'] ?? null,
                ]);
                $result['weights']['created']++;
            }

            foreach ($data['mortalities'] ?? [] as $m) {
                if (MortalityRecord::where('client_uuid', $m['client_uuid'])->exists()) {
                    $result['mortalities']['duplicates']++;

                    continue;
                }
                if (! empty($m['pig_id'])) {
                    $pig = Pig::find($m['pig_id']);
                    $m['batch_id'] = $pig->batch_id;
                    $m['count'] = 1;
                    $pig->update(['status' => 'dead']);
                }
                MortalityRecord::create([
                    'client_uuid' => $m['client_uuid'],
                    'pig_id' => $m['pig_id'] ?? null,
                    'batch_id' => $m['batch_id'] ?? null,
                    'died_on' => $m['died_on'],
                    'cause' => $m['cause'] ?? null,
                    'count' => $m['count'] ?? 1,
                ]);
                $result['mortalities']['created']++;
            }
        });

        return response()->json(['data' => $result]);
    }
}
