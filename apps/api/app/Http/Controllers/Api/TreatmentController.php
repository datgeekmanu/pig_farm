<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Treatment\StoreTreatmentRequest;
use App\Http\Requests\Treatment\UpdateTreatmentRequest;
use App\Http\Resources\TreatmentResource;
use App\Models\StockItem;
use App\Models\Treatment;
use App\Support\StockLedger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class TreatmentController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Treatment::query()
            ->when($request->filled('scope'), fn ($q) => $q->where('scope', $request->string('scope')))
            ->when($request->filled('batch_id'), fn ($q) => $q->where('batch_id', $request->integer('batch_id')))
            ->when($request->filled('pig_id'), fn ($q) => $q->where('pig_id', $request->integer('pig_id')));

        return TreatmentResource::collection($query->latest('date')->paginate(20));
    }

    public function store(StoreTreatmentRequest $request): JsonResponse
    {
        $data = $request->validated();

        try {
            $treatment = DB::transaction(function () use ($data) {
                $treatment = Treatment::create($data);

                // Consume linked veterinary stock, if any.
                if (! empty($data['stock_item_id']) && ! empty($data['quantite_utilisee'])) {
                    $item = StockItem::findOrFail($data['stock_item_id']);
                    StockLedger::apply($item, -(float) $data['quantite_utilisee'], 'traitement');
                }

                return $treatment;
            });
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return TreatmentResource::make($treatment)->response()->setStatusCode(201);
    }

    public function show(Treatment $treatment): TreatmentResource
    {
        return TreatmentResource::make($treatment);
    }

    public function update(UpdateTreatmentRequest $request, Treatment $treatment): TreatmentResource
    {
        $treatment->update($request->validated());

        return TreatmentResource::make($treatment);
    }

    public function destroy(Treatment $treatment): JsonResponse
    {
        $treatment->delete();

        return response()->json(['message' => 'Treatment deleted.']);
    }
}
