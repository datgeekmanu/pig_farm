<?php

namespace App\Http\Resources;

use App\Models\Breeding;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Breeding */
class BreedingResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'sow_id' => $this->sow_id,
            'boar_id' => $this->boar_id,
            'band_id' => $this->band_id,
            'bred_on' => $this->bred_on?->toDateString(),
            'method' => $this->method,
            'expected_farrow_on' => $this->expected_farrow_on?->toDateString(),
            'ultrasound_on' => $this->ultrasound_on?->toDateString(),
            'ultrasound_result' => $this->ultrasound_result,
            'heat_return_on' => $this->heat_return_on?->toDateString(),
            'status' => $this->status,
            'notes' => $this->notes,
        ];
    }
}
