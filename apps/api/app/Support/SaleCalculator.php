<?php

namespace App\Support;

class SaleCalculator
{
    // Line total in minor units: poids × prix/kg, then discount, then tax.
    public static function lineTotal(array $item): int
    {
        $subtotal = (float) ($item['poids_total_kg'] ?? 0) * (float) ($item['prix_kg'] ?? 0);
        $remise = (float) ($item['remise_pct'] ?? 0);
        $taxe = (float) ($item['taxe_pct'] ?? 0);

        return (int) round($subtotal * (1 - $remise / 100) * (1 + $taxe / 100));
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     */
    public static function total(array $items): int
    {
        return array_sum(array_map(self::lineTotal(...), $items));
    }
}
