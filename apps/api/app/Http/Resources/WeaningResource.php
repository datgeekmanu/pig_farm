<?php

namespace App\Http\Resources;

use App\Models\Weaning;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Weaning */
class WeaningResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'farrowing_id' => $this->farrowing_id,
            'weaned_on' => $this->weaned_on?->toDateString(),
            'weaned_count' => $this->weaned_count,
            'avg_weight_kg' => $this->avg_weight_kg,
            'notes' => $this->notes,
        ];
    }
}
