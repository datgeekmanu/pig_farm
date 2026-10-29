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
        // ?months=n is a shortcut for "the last n calendar months".
        $from = $request->filled('months')
            ? now()->subMonths((int) $request->integer('months') - 1)->startOfMonth()->toDateString()
            : $request->date('from')?->toDateString();

        $summary = FinanceSummary::compute($from, $request->date('to')?->toDateString());

        $farm = Farm::find(app(CurrentFarm::class)->id());
        $summary['seuil_rentabilite'] = $farm?->seuil_rentabilite;

        return response()->json(['data' => $summary]);
    }
}
