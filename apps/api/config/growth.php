<?php

// Reference growth curve for the dashboard "Courbe de croissance" (ref vs actual).
// Values are APPROXIMATE, IFIP-style (recommended live weight by age in weeks).
// ponytail: placeholder reference — swap for official IFIP tables or per-farm
// targets when available; `band_pct` widens the min/max envelope around `rec`.
return [

    'band_pct' => 0.10, // ±10% min/max envelope around the recommended weight

    // week => recommended live weight (kg)
    'recommended_kg' => [
        0 => 1.5, 1 => 2.5, 2 => 4, 3 => 6, 4 => 8,
        5 => 10, 6 => 13, 7 => 16, 8 => 20,
        9 => 24, 10 => 28, 11 => 33, 12 => 38, 13 => 44, 14 => 50, 15 => 57, 16 => 64,
        17 => 71, 18 => 78, 19 => 85, 20 => 92, 21 => 99, 22 => 106, 23 => 112, 24 => 118,
    ],

    // phase boundaries by week (label => [start_week, end_week])
    'phases' => [
        'sevrage' => [0, 4],
        'demarrage' => [4, 8],
        'croissance' => [8, 16],
        'finition' => [16, 24],
    ],

    // Phase derived from average live weight (kg) — matches the field app labels.
    // Each entry: live weight below `max` (kg) → `label`; last entry has null max.
    'weight_phases' => [
        ['max' => 15, 'label' => 'Post-sevrage'],
        ['max' => 30, 'label' => 'Démarrage'],
        ['max' => 70, 'label' => 'Croissance'],
        ['max' => null, 'label' => 'Finition'],
    ],
];
