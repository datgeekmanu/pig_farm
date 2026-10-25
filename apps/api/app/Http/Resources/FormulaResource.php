<?php

namespace App\Http\Resources;

use App\Models\Formula;
use App\Models\FormulaLine;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Formula */
class FormulaResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nom' => $this->nom,
            'phase' => $this->phase,
            'mode' => $this->mode,
            'poids_total_kg' => $this->poids_total_kg,
            'cost_per_kg' => $this->cost_per_kg,
            'lines' => $this->whenLoaded('lines', fn () => $this->lines->map(fn (FormulaLine $l) => [
                'ingredient_id' => $l->ingredient_id,
                'ingredient_nom' => $l->ingredient?->nom,
                'percent' => (float) $l->percent,
            ])),
            'created_at' => $this->created_at,
        ];
    }
}
