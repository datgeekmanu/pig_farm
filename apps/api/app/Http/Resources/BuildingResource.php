<?php

namespace App\Http\Resources;

use App\Models\Building;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Building */
class BuildingResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nom' => $this->nom,
            'surface_m2' => $this->surface_m2,
            'notes' => $this->notes,
            'batches_count' => $this->whenCounted('batches'),
        ];
    }
}
