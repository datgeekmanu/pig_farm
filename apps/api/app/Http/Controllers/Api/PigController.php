<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Pig\StorePigRequest;
use App\Http\Requests\Pig\UpdatePigRequest;
use App\Http\Resources\PigResource;
use App\Models\Pig;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PigController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return PigResource::collection(Pig::latest()->paginate(20));
    }

    public function store(StorePigRequest $request): JsonResponse
    {
        $pig = Pig::create($request->validated());

        return PigResource::make($pig)->response()->setStatusCode(201);
    }

    public function show(Pig $pig): PigResource
    {
        return PigResource::make($pig);
    }

    public function update(UpdatePigRequest $request, Pig $pig): PigResource
    {
        $pig->update($request->validated());

        return PigResource::make($pig);
    }

    public function destroy(Pig $pig): JsonResponse
    {
        $pig->delete();

        return response()->json(['message' => 'Pig deleted.']);
    }
}
