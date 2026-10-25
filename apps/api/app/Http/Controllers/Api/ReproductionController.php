<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BreedingResource;
use App\Http\Resources\PigResource;
use App\Models\Breeding;
use App\Models\Farrowing;
use App\Models\Pig;
use App\Models\Weaning;
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
        [$from, $to] = [$request->date('from'), $request->date('to')];

        $breedings = Breeding::query()
            ->when($from, fn ($q) => $q->whereDate('bred_on', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('bred_on', '<=', $to));

        $farrowings = Farrowing::query()
            ->when($from, fn ($q) => $q->whereDate('farrowed_on', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('farrowed_on', '<=', $to));

        $nbSaillies = (clone $breedings)->count();
        $nbMisesBas = (clone $farrowings)->count();
        $bornAlive = (int) (clone $farrowings)->sum('born_alive');
        $bornDead = (int) (clone $farrowings)->sum('born_dead');
        $weaned = (int) Weaning::query()
            ->when($from, fn ($q) => $q->whereDate('weaned_on', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('weaned_on', '<=', $to))
            ->sum('weaned_count');

        return response()->json(['data' => [
            'nb_saillies' => $nbSaillies,
            'nb_mises_bas' => $nbMisesBas,
            'taux_mise_bas_pct' => $nbSaillies > 0 ? round($nbMisesBas / $nbSaillies * 100, 1) : null,
            'nes_vivants_total' => $bornAlive,
            'nes_morts_total' => $bornDead,
            'nes_vivants_moyen' => $nbMisesBas > 0 ? round($bornAlive / $nbMisesBas, 2) : null,
            'sevres_total' => $weaned,
            'mortalite_allaitement_pct' => $bornAlive > 0 ? round(max(0, $bornAlive - $weaned) / $bornAlive * 100, 1) : null,
        ]]);
    }
}
