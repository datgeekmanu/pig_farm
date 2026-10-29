<?php

namespace App\Http\Resources;

use App\Models\Treatment;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Treatment */
class TreatmentResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'scope' => $this->scope,
            'batch_id' => $this->batch_id,
            'pig_id' => $this->pig_id,
            'date' => $this->date?->toDateString(),
            'produit' => $this->produit,
            'dose' => $this->dose,
            'type' => $this->type,
            'motif' => $this->motif,
            'soignant' => $this->soignant,
            'soignant_tel' => $this->soignant_tel,
            'cout' => $this->cout,
            'date_rappel' => $this->date_rappel?->toDateString(),
            'stock_item_id' => $this->stock_item_id,
            'quantite_utilisee' => $this->quantite_utilisee,
            'notes' => $this->notes,
        ];
    }
}
