<?php

namespace App\Http\Resources;

use App\Models\StockItem;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin StockItem */
class StockItemResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'nom' => $this->nom,
            'categorie' => $this->categorie,
            'quantite' => $this->quantite,
            'unite' => $this->unite,
            'seuil_alerte' => $this->seuil_alerte,
            'prix_unitaire' => $this->prix_unitaire,
            'date' => $this->date?->toDateString(),
            'is_low' => $this->is_low,
            'notes' => $this->notes,
        ];
    }
}
