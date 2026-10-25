<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Farm;
use App\Support\CurrentFarm;
use App\Support\FinanceSummary;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FinanceController extends Controller
{
    // Finance dashboard: CA / dépenses / bénéfice brut + monthly series + category breakdown.
    public function overview(Request $request): JsonResponse
    {
        $summary = FinanceSummary::compute(
            $request->date('from')?->toDateString(),
            $request->date('to')?->toDateString(),
        );

        $farm = Farm::find(app(CurrentFarm::class)->id());
        $summary['seuil_rentabilite'] = $farm?->seuil_rentabilite;

        return response()->json(['data' => $summary]);
    }
}
