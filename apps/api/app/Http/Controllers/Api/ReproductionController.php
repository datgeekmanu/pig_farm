<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BreedingResource;
use App\Http\Resources\PigResource;
use App\Models\Breeding;
use App\Models\Pig;
use App\Support\ReproStats;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ReproductionController extends Controller
{
    // Truies vides — sows without an active (saillie/gestante) breeding: to be served.
    public function emptySows(): AnonymousResourceCollection
    {
        $active = Breeding::whereIn('status', ['saillie', 'gestante'])->pluck('sow_id');

        return PigResource::collection(
            Pig::where('categorie', 'truie')->where('status', 'active')->whereNotIn('id', $active)->get()
        );
    }

    // Fiche reproducteur — a breeder's career: all breedings where it was sow or boar.
    public function breedingHistory(Pig $pig): AnonymousResourceCollection
    {
        return BreedingResource::collection(
            Breeding::where('sow_id', $pig->id)->orWhere('boar_id', $pig->id)
                ->latest('bred_on')->get()
        );
    }

    // GTTT — technical indicators of the sow herd.
    public function gtt(Request $request): JsonResponse
    {
        return response()->json(['data' => ReproStats::gtt(
            $request->date('from')?->toDateString(),
            $request->date('to')?->toDateString(),
        )]);
    }
}
