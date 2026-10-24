<?php

namespace App\Http\Resources;

use App\Models\WeightRecord;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin WeightRecord */
class WeightResource extends JsonResource
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
            'weight_kg' => $this->weight_kg,
            'weighed_on' => $this->weighed_on?->toDateString(),
            'notes' => $this->notes,
        ];
    }
}
