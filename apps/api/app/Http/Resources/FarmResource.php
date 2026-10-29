<?php

namespace App\Http\Resources;

use App\Models\Farm;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Farm */
class FarmResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'currency' => $this->currency,
            // present only when loaded via the user<->farm pivot
            'role' => $this->whenPivotLoaded('farm_user', fn () => $this->pivot->role),
        ];
    }
}
