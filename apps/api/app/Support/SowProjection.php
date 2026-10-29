<?php

namespace App\Support;

use App\Models\Breeding;
use App\Models\Farrowing;
use App\Models\Pig;
use App\Models\Weaning;

// Projects a sow (pig, categorie=truie) into its current reproduction state for
// the field/dashboard apps, from its breedings + farrowings.
class SowProjection
{
    /**
     * @return array<string, mixed>
     */
    public static function for(Pig $sow): array
    {
        $breedings = Breeding::where('sow_id', $sow->id)->orderBy('bred_on')->get();
        $farrowings = Farrowing::where('sow_id', $sow->id)->orderBy('farrowed_on')->get();
        $breeding = $breedings->last();
        $farrowing = $farrowings->last();

        $status = 'vide';
        if ($breeding && in_array($breeding->status, ['saillie', 'gestante'], true)) {
            $status = 'gestante';
        }
        if ($farrowing) {
            $weaned = Weaning::where('farrowing_id', $farrowing->id)->exists();
            if (! $weaned && (! $breeding || $farrowing->farrowed_on->gte($breeding->bred_on))) {
                $status = 'allaitante';
            }
        }

        $totalBorn = (int) $farrowings->sum('born_alive');
        $totalWeaned = (int) Weaning::whereIn('farrowing_id', $farrowings->pluck('id'))->sum('weaned_count');

        return [
            'id' => $sow->id,
            'code' => $sow->identifiant,
            'name' => $sow->nom,
            'status' => $status, // gestante | vide | allaitante
            'serviceDate' => $status === 'gestante' ? $breeding?->bred_on?->toDateString() : null,
            'boar' => $breeding?->boar_id ? Pig::find($breeding->boar_id)?->identifiant : null,
            'ultrasoundConfirmed' => $breeding?->ultrasound_result === 'gestante',
            'parity' => $farrowings->count(),
            'bornAlivePerLitter' => $farrowings->count() ? round($farrowings->avg('born_alive'), 1) : 0,
            'weaningSurvivalPct' => $totalBorn ? round($totalWeaned / $totalBorn * 100, 1) : null,
            'cycle' => [
                ['key' => 'service', 'date' => $breeding?->bred_on?->toDateString(), 'done' => (bool) $breeding],
                ['key' => 'ultrasound', 'date' => $breeding?->ultrasound_on?->toDateString(), 'done' => $breeding?->ultrasound_result !== null],
                ['key' => 'farrowing', 'date' => ($farrowing?->farrowed_on ?? $breeding?->expected_farrow_on)?->toDateString(), 'done' => (bool) $farrowing],
            ],
        ];
    }
}
