<?php

namespace App\Http\Resources;

use App\Models\Ingredient;
use App\Models\Nutrient;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Ingredient */
class IngredientResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nom' => $this->nom,
            'prix_kg' => $this->prix_kg,
            'nutrients' => $this->whenLoaded('nutrients', fn () => $this->nutrients->map(fn (Nutrient $n) => [
                'id' => $n->id,
                'code' => $n->code,
                'nom' => $n->nom,
                'unit' => $n->unit,
                'value' => (float) $n->pivot->value,
            ])),
        ];
    }
}
