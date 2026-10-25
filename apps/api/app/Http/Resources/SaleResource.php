<?php

namespace App\Http\Resources;

use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Sale */
class SaleResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'date_vente' => $this->date_vente?->toDateString(),
            'acheteur' => $this->acheteur,
            'acheteur_tel' => $this->acheteur_tel,
            'acheteur_email' => $this->acheteur_email,
            'mode_paiement' => $this->mode_paiement,
            'statut_paiement' => $this->statut_paiement,
            'total' => $this->total,
            'notes' => $this->notes,
            'sujets_vendus' => $this->whenLoaded('items', fn () => $this->items->sum('quantite')),
            'items' => $this->whenLoaded('items', fn () => $this->items->map(fn (SaleItem $i) => [
                'id' => $i->id,
                'batch_id' => $i->batch_id,
                'pig_id' => $i->pig_id,
                'type_porc' => $i->type_porc,
                'quantite' => $i->quantite,
                'poids_total_kg' => $i->poids_total_kg,
                'prix_kg' => $i->prix_kg,
                'age_mois' => $i->age_mois,
                'remise_pct' => $i->remise_pct,
                'taxe_pct' => $i->taxe_pct,
                'line_total' => $i->line_total,
            ])),
        ];
    }
}
