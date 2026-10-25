<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Band\StoreBandRequest;
use App\Http\Requests\Band\UpdateBandRequest;
use App\Http\Resources\BandResource;
use App\Models\Band;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class BandController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return BandResource::collection(Band::withCount('breedings')->latest()->get());
    }

    public function store(StoreBandRequest $request): JsonResponse
    {
        $band = Band::create($request->validated());

        return BandResource::make($band)->response()->setStatusCode(201);
    }

    public function show(Band $band): BandResource
    {
        return BandResource::make($band->loadCount('breedings'));
    }

    public function update(UpdateBandRequest $request, Band $band): BandResource
    {
        $band->update($request->validated());

        return BandResource::make($band);
    }

    public function destroy(Band $band): JsonResponse
    {
        $band->delete();

        return response()->json(['message' => 'Band deleted.']);
    }
}
