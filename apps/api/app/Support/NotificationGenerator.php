<?php

namespace App\Support;

use App\Models\Breeding;
use App\Models\FarmNotification;
use App\Models\Farrowing;
use App\Models\StockItem;
use App\Models\Treatment;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

// Generates in-app alerts for the active farm. Idempotent: a dedupe_key prevents
// the same event from alerting twice. Run inside a CurrentFarm context.
class NotificationGenerator
{
    /**
     * @return Collection<int, FarmNotification>
     */
    public static function forCurrentFarm(): Collection
    {
        $today = Carbon::today();
        $soon = $today->copy()->addDays(3);
        $new = collect();

        foreach (StockItem::whereNotNull('seuil_alerte')->whereColumn('quantite', '<=', 'seuil_alerte')->get() as $it) {
            $new->push(self::make('stock_bas', "Stock bas : {$it->nom}",
                "Quantité {$it->quantite} ≤ seuil {$it->seuil_alerte}", ['stock_item_id' => $it->id],
                "stock_bas:{$it->id}:{$today->toDateString()}"));
        }

        foreach (Breeding::whereIn('status', ['saillie', 'gestante'])->whereBetween('expected_farrow_on', [$today, $soon])->get() as $b) {
            $new->push(self::make('mise_bas_prevue', 'Mise bas prévue',
                "Truie #{$b->sow_id} — prévue le {$b->expected_farrow_on->format('d/m/Y')}", ['breeding_id' => $b->id],
                "mise_bas:{$b->id}"));
        }

        foreach (Treatment::whereNotNull('date_rappel')->whereBetween('date_rappel', [$today, $soon])->get() as $t) {
            $new->push(self::make('vaccin_rappel', 'Rappel de traitement',
                "{$t->produit} — rappel le {$t->date_rappel->format('d/m/Y')}", ['treatment_id' => $t->id],
                "rappel:{$t->id}"));
        }

        foreach (Breeding::whereNotNull('heat_return_on')->whereBetween('heat_return_on', [$today, $soon])->get() as $b) {
            $new->push(self::make('retour_chaleur', 'Retour en chaleur',
                "Truie #{$b->sow_id} — {$b->heat_return_on->format('d/m/Y')}", ['breeding_id' => $b->id],
                "retour:{$b->id}"));
        }

        $weanAge = (int) config('reproduction.weaning_age_days');
        foreach (Farrowing::whereDoesntHave('weaning')->get() as $f) {
            if ($f->farrowed_on->copy()->addDays($weanAge)->lte($today)) {
                $new->push(self::make('sevrage_du', 'Sevrage dû',
                    "Portée du {$f->farrowed_on->format('d/m/Y')} — sevrage recommandé", ['farrowing_id' => $f->id],
                    "sevrage:{$f->id}"));
            }
        }

        return $new->filter()->values();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private static function make(string $type, string $title, string $body, array $data, string $dedupeKey): ?FarmNotification
    {
        if (FarmNotification::where('dedupe_key', $dedupeKey)->exists()) {
            return null;
        }

        return FarmNotification::create([
            'type' => $type, 'title' => $title, 'body' => $body, 'data' => $data, 'dedupe_key' => $dedupeKey,
        ]);
    }
}
