<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Batch\StoreBatchRequest;
use App\Http\Requests\Batch\UpdateBatchRequest;
use App\Http\Resources\BatchResource;
use App\Models\Batch;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

// Farm scoping is automatic: BelongsToFarm filters queries and route-model
// binding by the active farm (foreign ids resolve to 404).
class BatchController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return BatchResource::collection(
            Batch::withCount('pigs')->latest()->paginate(20)
        );
    }

    public function store(StoreBatchRequest $request): JsonResponse
    {
        $batch = Batch::create($request->validated());

        return BatchResource::make($batch)->response()->setStatusCode(201);
    }

    public function show(Batch $batch): BatchResource
    {
        return BatchResource::make($batch->loadCount('pigs'));
    }

    public function update(UpdateBatchRequest $request, Batch $batch): BatchResource
    {
        $batch->update($request->validated());

        return BatchResource::make($batch);
    }

    public function destroy(Batch $batch): JsonResponse
    {
        $batch->delete();

        return response()->json(['message' => 'Batch deleted.']);
    }
}
