<?php

namespace App\Http\Resources;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

/** @mixin Product */
class ProductResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'farm_id' => $this->farm_id,
            'batch_id' => $this->batch_id,
            'type' => $this->type,
            'nom' => $this->nom,
            'description' => $this->description,
            'unit' => $this->unit,
            'prix' => $this->prix,
            'stock_qty' => $this->stock_qty,
            'is_published' => $this->is_published,
            'photo_url' => $this->photo_path ? Storage::url($this->photo_path) : null,
        ];
    }
}
