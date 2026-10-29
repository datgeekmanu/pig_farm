<?php

namespace App\Support;

use App\Models\Breeding;
use App\Models\Farrowing;
use App\Models\Weaning;

class ReproStats
{
    /**
     * GTTT indicators for the sow herd over a period. Farm-scoped automatically.
     *
     * @return array<string, mixed>
     */
    public static function gtt(?string $from, ?string $to): array
    {
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

        return [
            'nb_saillies' => $nbSaillies,
            'nb_mises_bas' => $nbMisesBas,
            'taux_mise_bas_pct' => $nbSaillies > 0 ? round($nbMisesBas / $nbSaillies * 100, 1) : null,
            'nes_vivants_total' => $bornAlive,
            'nes_morts_total' => $bornDead,
            'nes_vivants_moyen' => $nbMisesBas > 0 ? round($bornAlive / $nbMisesBas, 2) : null,
            'sevres_total' => $weaned,
            'mortalite_allaitement_pct' => $bornAlive > 0 ? round(max(0, $bornAlive - $weaned) / $bornAlive * 100, 1) : null,
        ];
    }
}
