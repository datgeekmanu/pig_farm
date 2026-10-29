<?php

namespace App\Http\Resources;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Order */
class OrderResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'farm_id' => $this->farm_id,
            'buyer_id' => $this->buyer_id,
            'status' => $this->status,
            'total' => $this->total,
            'delivery_address' => $this->delivery_address,
            'delivery_phone' => $this->delivery_phone,
            'notes' => $this->notes,
            'placed_at' => $this->placed_at,
            'items' => $this->whenLoaded('items', fn () => $this->items->map(fn (OrderItem $i) => [
                'id' => $i->id,
                'product_id' => $i->product_id,
                'nom' => $i->nom,
                'unit' => $i->unit,
                'prix' => $i->prix,
                'quantity' => $i->quantity,
                'line_total' => $i->line_total,
            ])),
            'payment' => $this->whenLoaded('payment', fn () => [
                'amount' => $this->payment->amount,
                'method' => $this->payment->method,
                'status' => $this->payment->status,
                'reference' => $this->payment->reference,
            ]),
        ];
    }
}
