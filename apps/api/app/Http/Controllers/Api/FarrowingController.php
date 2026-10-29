<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Farrowing\StoreFarrowingRequest;
use App\Http\Requests\Farrowing\UpdateFarrowingRequest;
use App\Http\Resources\FarrowingResource;
use App\Models\Breeding;
use App\Models\Farrowing;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;

class FarrowingController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Farrowing::query()
            ->when($request->filled('sow_id'), fn ($q) => $q->where('sow_id', $request->integer('sow_id')))
            ->when($request->filled('breeding_id'), fn ($q) => $q->where('breeding_id', $request->integer('breeding_id')));

        return FarrowingResource::collection($query->with('weaning')->latest('farrowed_on')->paginate(20));
    }

    public function store(StoreFarrowingRequest $request): JsonResponse
    {
        $data = $request->validated();

        $farrowing = DB::transaction(function () use ($data) {
            // Close the gestation and inherit its sow if one was supplied.
            if (! empty($data['breeding_id'])) {
                $breeding = Breeding::findOrFail($data['breeding_id']);
                $data['sow_id'] ??= $breeding->sow_id;
                $breeding->update(['status' => 'mise_bas']);
            }

            return Farrowing::create($data);
        });

        return FarrowingResource::make($farrowing)->response()->setStatusCode(201);
    }

    public function show(Farrowing $farrowing): FarrowingResource
    {
        return FarrowingResource::make($farrowing->load('weaning'));
    }

    public function update(UpdateFarrowingRequest $request, Farrowing $farrowing): FarrowingResource
    {
        $farrowing->update($request->validated());

        return FarrowingResource::make($farrowing);
    }

    public function destroy(Farrowing $farrowing): JsonResponse
    {
        $farrowing->delete();

        return response()->json(['message' => 'Farrowing deleted.']);
    }
}
