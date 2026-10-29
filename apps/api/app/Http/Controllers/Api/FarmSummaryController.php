<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Batch;
use App\Models\Farm;
use App\Models\FarmNotification;
use App\Models\MortalityRecord;
use App\Models\SaleItem;
use App\Support\CurrentFarm;
use App\Support\LotStats;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class FarmSummaryController extends Controller
{
    // Herd KPIs + user block for the home screens.
    public function show(Request $request): JsonResponse
    {
        $farm = Farm::find(app(CurrentFarm::class)->id());
        $batches = Batch::all();

        $total = $batches->sum(fn (Batch $b) => $b->currentHead());
        $metrics = $batches->map(fn (Batch $b) => LotStats::for($b));
        $adg = $metrics->pluck('adg_g_per_day')->filter(fn ($v) => $v !== null);
        $fcr = $metrics->pluck('feed_conversion')->filter(fn ($v) => $v !== null);

        $initial = (int) $batches->sum('effectif_initial');
        $deaths = (int) MortalityRecord::sum('count');

        $weekAgo = Carbon::today()->subDays(7);
        $newHead = (int) Batch::whereDate('date_mise_en_lot', '>=', $weekAgo)->sum('effectif_initial');
        $deaths7 = (int) MortalityRecord::whereDate('died_on', '>=', $weekAgo)->sum('count');
        $sold7 = (int) SaleItem::whereHas('sale', fn ($q) => $q->whereDate('date_vente', '>=', $weekAgo))->sum('quantite');

        $firstName = Str::of((string) $request->user()->name)->trim()->explode(' ')->first() ?? '';

        return response()->json(['data' => [
            'id' => $farm?->id,
            'name' => $farm?->name,
            'currency' => $farm?->currency,
            'user' => [
                'firstName' => $firstName,
                'initials' => Str::upper(Str::substr($firstName, 0, 1)),
                'unreadNotifications' => FarmNotification::unread()->count(),
            ],
            'herd' => [
                'total' => $total,
                'weeklyChange' => $newHead - $deaths7 - $sold7,
                'adgGramsPerDay' => $adg->isNotEmpty() ? (int) round($adg->avg()) : null,
                'mortalityPct' => $initial > 0 ? round($deaths / $initial * 100, 1) : 0,
                'feedConversion' => $fcr->isNotEmpty() ? round($fcr->avg(), 2) : null,
            ],
        ]]);
    }
}
