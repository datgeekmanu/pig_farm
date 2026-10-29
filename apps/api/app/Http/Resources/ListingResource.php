<?php

namespace App\Http\Resources;

use App\Models\Product;
use App\Support\LotStats;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

/** @mixin Product */
class ListingResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $batch = $this->batch; // may be null
        $available = $batch ? $batch->currentHead() : (int) $this->stock_qty;
        $averageKg = $batch ? LotStats::for($batch)['avg_weight_kg'] : null;

        return [
            'id' => $this->id,
            'lotId' => $this->batch_id,
            'category' => $this->category,
            'title' => $this->nom,
            'available' => $available,
            'averageKg' => $averageKg,
            'priceXofPerKg' => $this->prix,
            'marketXofPerKg' => $this->market_price,
            'quantity' => $this->stock_qty,
            'visible' => $this->visible,
            'published' => $this->is_published,
            'photo' => $this->photo_path ? Storage::url($this->photo_path) : null,
        ];
    }
}
