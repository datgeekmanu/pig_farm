<?php

namespace App\Http\Resources;

use App\Models\Salary;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Salary */
class SalaryResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nom_ouvrier' => $this->nom_ouvrier,
            'poste' => $this->poste,
            'montant' => $this->montant,
            'date_paiement' => $this->date_paiement?->toDateString(),
            'mois_concerne' => $this->mois_concerne,
            'notes' => $this->notes,
        ];
    }
}
