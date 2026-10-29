<?php

namespace App\Support;

use App\Models\Buyer;
use App\Models\Order;
use App\Models\Product;
use App\Models\Sale;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class OrderService
{
    /**
     * Place an order atomically: lock the products, verify stock, decrement it,
     * and record a Sale so the seller's herd count + finances reflect it — all in
     * one transaction (no overselling, no partial writes).
     *
     * @param  array{items:array<int,array{product_id:int,quantity:float}>, delivery_address?:string, delivery_phone?:string, notes?:string, payment_method?:string}  $data
     */
    public static function place(Buyer $buyer, array $data): Order
    {
        return DB::transaction(function () use ($buyer, $data) {
            $ids = collect($data['items'])->pluck('product_id');
            $products = Product::whereIn('id', $ids)->lockForUpdate()->get()->keyBy('id');

            $farmIds = $products->pluck('farm_id')->unique();
            if ($products->count() !== $ids->unique()->count() || $farmIds->count() !== 1) {
                throw ValidationException::withMessages(['items' => ['Articles invalides ou de fermes différentes.']]);
            }
            $farmId = $farmIds->first();
            // Scope herd/finance writes to the seller farm.
            app(CurrentFarm::class)->set($farmId);

            $total = 0;
            $orderItems = [];
            $saleItems = [];

            foreach ($data['items'] as $line) {
                $p = $products[$line['product_id']];
                $qty = (float) $line['quantity'];

                if (! $p->is_published) {
                    throw ValidationException::withMessages(['items' => ["Produit indisponible: {$p->nom}."]]);
                }
                if ((float) $p->stock_qty < $qty) {
                    throw ValidationException::withMessages(['items' => ["Stock insuffisant: {$p->nom}."]]);
                }

                $lineTotal = (int) round($p->prix * $qty);
                $total += $lineTotal;
                $p->decrement('stock_qty', $qty);

                $orderItems[] = ['product_id' => $p->id, 'nom' => $p->nom, 'unit' => $p->unit, 'prix' => $p->prix, 'quantity' => $qty, 'line_total' => $lineTotal];

                // Live pigs tied to a batch reduce the herd via a sale item.
                if ($p->type === 'live_pig' && $p->batch_id) {
                    $saleItems[] = ['batch_id' => $p->batch_id, 'type_porc' => $p->nom, 'quantite' => (int) $qty, 'line_total' => $lineTotal];
                }
            }

            $sale = Sale::create([
                'date_vente' => now()->toDateString(),
                'acheteur' => $buyer->name,
                'acheteur_tel' => $buyer->phone,
                'statut_paiement' => 'impaye',
                'total' => $total,
                'notes' => 'Commande storefront',
            ]);
            foreach ($saleItems as $si) {
                $sale->items()->create($si);
            }

            $order = Order::create([
                'farm_id' => $farmId,
                'buyer_id' => $buyer->id,
                'sale_id' => $sale->id,
                'status' => 'placed',
                'total' => $total,
                'delivery_address' => $data['delivery_address'] ?? null,
                'delivery_phone' => $data['delivery_phone'] ?? null,
                'notes' => $data['notes'] ?? null,
                'placed_at' => now(),
            ]);
            foreach ($orderItems as $oi) {
                $order->items()->create($oi);
            }
            $order->payment()->create([
                'amount' => $total,
                'method' => $data['payment_method'] ?? null,
                'status' => 'pending',
                'reference' => 'PAY-'.strtoupper(Str::random(10)),
            ]);

            return $order;
        });
    }

    // Cancel an order: restock products and void the linked sale (herd/finance).
    public static function cancel(Order $order): void
    {
        DB::transaction(function () use ($order) {
            foreach ($order->items as $item) {
                if ($item->product_id) {
                    Product::whereKey($item->product_id)->lockForUpdate()->first()?->increment('stock_qty', $item->quantity);
                }
            }
            if ($order->sale_id) {
                Sale::withoutGlobalScopes()->whereKey($order->sale_id)->delete();
            }
            $order->update(['status' => 'cancelled', 'sale_id' => null]);
            $order->payment?->update(['status' => 'failed']);
        });
    }
}
