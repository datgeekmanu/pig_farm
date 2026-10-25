<?php

namespace App\Http\Resources;

use App\Models\Farrowing;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Farrowing */
class FarrowingResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'breeding_id' => $this->breeding_id,
            'sow_id' => $this->sow_id,
            'farrowed_on' => $this->farrowed_on?->toDateString(),
            'born_alive' => $this->born_alive,
            'born_dead' => $this->born_dead,
            'mummies' => $this->mummies,
            'notes' => $this->notes,
            'weaning' => WeaningResource::make($this->whenLoaded('weaning')),
        ];
    }
}
