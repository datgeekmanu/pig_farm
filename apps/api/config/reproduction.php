<?php

return [
    // Average gestation length (days) used to predict farrowing dates.
    // ponytail: 114 is the textbook "3 months 3 weeks 3 days" — leave as a knob,
    // herds tune it slightly.
    'gestation_days' => 114,

    // Typical age at weaning (days) — used for planning hints.
    'weaning_age_days' => 28,
];
