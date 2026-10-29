<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Building\StoreBuildingRequest;
use App\Http\Requests\Building\UpdateBuildingRequest;
use App\Http\Resources\BuildingResource;
use App\Models\Building;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class BuildingController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return BuildingResource::collection(Building::withCount('batches')->get());
    }

    public function store(StoreBuildingRequest $request): JsonResponse
    {
        $building = Building::create($request->validated());

        return BuildingResource::make($building)->response()->setStatusCode(201);
    }

    public function show(Building $building): BuildingResource
    {
        return BuildingResource::make($building->loadCount('batches'));
    }

    public function update(UpdateBuildingRequest $request, Building $building): BuildingResource
    {
        $building->update($request->validated());

        return BuildingResource::make($building);
    }

    public function destroy(Building $building): JsonResponse
    {
        $building->delete();

        return response()->json(['message' => 'Building deleted.']);
    }

    // Densité: live head vs floor space, with a comfort flag against the min m²/pig norm.
    public function density(Building $building): JsonResponse
    {
        $head = $building->batches()->get()->sum(fn ($b) => $b->currentHead());
        $surface = (float) $building->surface_m2;
        $minPerPig = (float) config('herd.min_m2_per_pig');

        $m2PerHead = $head > 0 ? round($surface / $head, 3) : null;

        return response()->json(['data' => [
            'surface_m2' => $surface,
            'current_head' => $head,
            'density_per_m2' => $surface > 0 ? round($head / $surface, 3) : null,
            'm2_per_head' => $m2PerHead,
            'min_m2_per_head' => $minPerPig,
            'comfort' => $m2PerHead === null ? 'empty' : ($m2PerHead >= $minPerPig ? 'ok' : 'crowded'),
        ]]);
    }
}
