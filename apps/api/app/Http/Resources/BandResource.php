<?php

namespace App\Http\Resources;

use App\Models\Band;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Band */
class BandResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nom' => $this->nom,
            'taille_objectif' => $this->taille_objectif,
            'date_prevue_saillie' => $this->date_prevue_saillie?->toDateString(),
            'notes' => $this->notes,
            'breedings_count' => $this->whenCounted('breedings'),
        ];
    }
}
