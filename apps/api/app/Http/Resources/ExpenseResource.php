<?php

namespace App\Http\Resources;

use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Expense */
class ExpenseResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'batch_id' => $this->batch_id,
            'date' => $this->date?->toDateString(),
            'montant' => $this->montant,
            'categorie' => $this->categorie,
            'description' => $this->description,
            'notes' => $this->notes,
        ];
    }
}
