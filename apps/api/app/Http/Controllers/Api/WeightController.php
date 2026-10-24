<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Weight\StoreWeightRequest;
use App\Http\Resources\WeightResource;
use App\Models\Pig;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class WeightController extends Controller
{
    public function index(Pig $pig): AnonymousResourceCollection
    {
        return WeightResource::collection(
            $pig->weights()->orderBy('weighed_on')->get()
        );
    }

    public function store(StoreWeightRequest $request, Pig $pig): JsonResponse
    {
        $weight = $pig->weights()->create([
            ...$request->validated(),
            'batch_id' => $pig->batch_id, // denormalised for batch aggregation
            'weighed_on' => $request->validated('weighed_on') ?? now()->toDateString(),
        ]);

        return WeightResource::make($weight)->response()->setStatusCode(201);
    }
}
