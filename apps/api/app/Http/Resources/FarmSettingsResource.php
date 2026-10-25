<?php

namespace App\Http\Resources;

use App\Models\Farm;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

/** @mixin Farm */
class FarmSettingsResource extends JsonResource
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
            'seuil_rentabilite' => $this->seuil_rentabilite,
            'phone' => $this->phone,
            'email' => $this->email,
            'address' => $this->address,
            'payment_info' => $this->payment_info,
            'receipt_color' => $this->receipt_color,
            'logo_url' => $this->logo_path ? Storage::url($this->logo_path) : null,
            'notification_prefs' => $this->notification_prefs ?? (object) [],
        ];
    }
}
