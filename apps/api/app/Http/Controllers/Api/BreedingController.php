<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Breeding\StoreBreedingRequest;
use App\Http\Requests\Breeding\UpdateBreedingRequest;
use App\Http\Resources\BreedingResource;
use App\Models\Breeding;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Carbon;

class BreedingController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Breeding::query()
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('sow_id'), fn ($q) => $q->where('sow_id', $request->integer('sow_id')))
            ->when($request->filled('band_id'), fn ($q) => $q->where('band_id', $request->integer('band_id')));

        return BreedingResource::collection($query->latest('bred_on')->paginate(20));
    }

    public function store(StoreBreedingRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['expected_farrow_on'] = $this->expectedFarrow($data['bred_on']);
        $data['status'] = 'saillie';

        $breeding = Breeding::create($data);

        return BreedingResource::make($breeding)->response()->setStatusCode(201);
    }

    public function show(Breeding $breeding): BreedingResource
    {
        return BreedingResource::make($breeding);
    }

    public function update(UpdateBreedingRequest $request, Breeding $breeding): BreedingResource
    {
        $data = $request->validated();

        if (isset($data['bred_on'])) {
            $data['expected_farrow_on'] = $this->expectedFarrow($data['bred_on']);
        }
        // Derive status from the gestation outcome.
        if (array_key_exists('ultrasound_result', $data) && $data['ultrasound_result'] !== null) {
            $data['status'] = $data['ultrasound_result'] === 'gestante' ? 'gestante' : 'vide';
        }
        if (! empty($data['heat_return_on'])) {
            $data['status'] = 'vide';
        }

        $breeding->update($data);

        return BreedingResource::make($breeding);
    }

    public function destroy(Breeding $breeding): JsonResponse
    {
        $breeding->delete();

        return response()->json(['message' => 'Breeding deleted.']);
    }

    private function expectedFarrow(string $bredOn): string
    {
        return Carbon::parse($bredOn)->addDays((int) config('reproduction.gestation_days'))->toDateString();
    }
}
