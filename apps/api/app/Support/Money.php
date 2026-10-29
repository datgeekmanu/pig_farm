<?php

namespace App\Support;

class Money
{
    // Minor units -> human string. XOF has no minor unit; EUR has 2 decimals.
    public static function format(?int $minor, string $currency): string
    {
        if ($minor === null) {
            return '—';
        }

        return match ($currency) {
            'EUR' => number_format($minor / 100, 2, ',', ' ').' €',
            default => number_format($minor, 0, ',', ' ').' FCFA',
        };
    }
}
