<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Weaning\StoreWeaningRequest;
use App\Http\Resources\WeaningResource;
use App\Models\Weaning;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class WeaningController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Weaning::query()
            ->when($request->filled('farrowing_id'), fn ($q) => $q->where('farrowing_id', $request->integer('farrowing_id')));

        return WeaningResource::collection($query->latest('weaned_on')->paginate(20));
    }

    public function store(StoreWeaningRequest $request): JsonResponse
    {
        $weaning = Weaning::create($request->validated());

        return WeaningResource::make($weaning)->response()->setStatusCode(201);
    }

    public function show(Weaning $weaning): WeaningResource
    {
        return WeaningResource::make($weaning);
    }

    public function update(StoreWeaningRequest $request, Weaning $weaning): WeaningResource
    {
        $weaning->update($request->validated());

        return WeaningResource::make($weaning);
    }

    public function destroy(Weaning $weaning): JsonResponse
    {
        $weaning->delete();

        return response()->json(['message' => 'Weaning deleted.']);
    }
}
