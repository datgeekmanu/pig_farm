<?php

namespace App\Http\Resources;

use App\Models\Batch;
use App\Support\LotStats;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Batch */
class BatchResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nom' => $this->nom,
            'batiment_loge' => $this->batiment_loge,
            'date_mise_en_lot' => $this->date_mise_en_lot?->toDateString(),
            'effectif_initial' => $this->effectif_initial,
            'date_naissance' => $this->date_naissance?->toDateString(),
            'poids_moyen_initial_kg' => $this->poids_moyen_initial_kg,
            'prix_achat_unitaire' => $this->prix_achat_unitaire,
            'building_id' => $this->building_id,
            'pen' => $this->batiment_loge,
            'status' => $this->status,
            'notes' => $this->notes,
            'pigs_count' => $this->whenCounted('pigs'),
            'created_at' => $this->created_at,
            // computed lot metrics (headcount, avg_weight_kg, uniformity_cv,
            // adg_g_per_day, feed_conversion, phase, age_days)
            ...LotStats::for($this->resource),
        ];
    }
}
