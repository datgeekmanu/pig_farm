<?php

namespace App\Http\Resources;

use App\Models\Pig;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Pig */
class PigResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'batch_id' => $this->batch_id,
            'identifiant' => $this->identifiant,
            'nom' => $this->nom,
            'sexe' => $this->sexe,
            'categorie' => $this->categorie,
            'date_naissance' => $this->date_naissance?->toDateString(),
            'date_arrivee' => $this->date_arrivee?->toDateString(),
            'date_inconnue' => $this->date_inconnue,
            'poids_arrivee_kg' => $this->poids_arrivee_kg,
            'prix_achat' => $this->prix_achat,
            'valeur_reforme' => $this->valeur_reforme,
            'provenance' => $this->provenance,
            'pere_id' => $this->pere_id,
            'mere_id' => $this->mere_id,
            'pere_nom' => $this->pere_nom,
            'mere_nom' => $this->mere_nom,
            'status' => $this->status,
            'notes' => $this->notes,
            'created_at' => $this->created_at,
        ];
    }
}
