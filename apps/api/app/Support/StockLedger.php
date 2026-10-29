<?php

namespace App\Support;

use App\Models\StockItem;
use App\Models\StockMovement;
use RuntimeException;

class StockLedger
{
    // Apply a signed change to a stock item and record the movement. Caller wraps
    // in a transaction. Throws if an outflow would drive the balance negative.
    public static function apply(StockItem $item, float $change, string $reason, ?string $notes = null): StockMovement
    {
        if ((float) $item->quantite + $change < 0) {
            throw new RuntimeException('Stock insuffisant pour cette sortie.');
        }

        $item->increment('quantite', $change);

        return $item->movements()->create([
            'change' => $change,
            'reason' => $reason,
            'notes' => $notes,
        ]);
    }
}
