<?php

namespace App\Http\Resources;

use App\Models\Nutrient;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Nutrient */
class NutrientResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'nom' => $this->nom,
            'unit' => $this->unit,
        ];
    }
}
