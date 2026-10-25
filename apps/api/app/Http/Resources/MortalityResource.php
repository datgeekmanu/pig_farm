<?php

namespace App\Http\Resources;

use App\Models\MortalityRecord;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin MortalityRecord */
class MortalityResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'pig_id' => $this->pig_id,
            'batch_id' => $this->batch_id,
            'died_on' => $this->died_on?->toDateString(),
            'cause' => $this->cause,
            'count' => $this->count,
            'weight_kg' => $this->weight_kg,
            'notes' => $this->notes,
        ];
    }
}
