<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pig;
use App\Support\SowProjection;
use Illuminate\Http\JsonResponse;

// Read projection of sows (pigs, categorie=truie) with their current repro state.
class SowController extends Controller
{
    public function index(): JsonResponse
    {
        $sows = Pig::where('categorie', 'truie')->where('status', 'active')->get()
            ->map(fn (Pig $sow) => SowProjection::for($sow))
            ->values();

        return response()->json(['data' => $sows]);
    }

    public function show(Pig $pig): JsonResponse
    {
        return response()->json(['data' => SowProjection::for($pig)]);
    }
}
