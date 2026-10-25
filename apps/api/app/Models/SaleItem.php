<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaleItem extends Model
{
    protected $fillable = [
        'sale_id', 'batch_id', 'pig_id', 'type_porc', 'quantite',
        'poids_total_kg', 'prix_kg', 'age_mois', 'remise_pct', 'taxe_pct', 'line_total',
    ];

    protected function casts(): array
    {
        return [
            'quantite' => 'integer',
            'poids_total_kg' => 'decimal:2',
            'prix_kg' => 'integer',
            'age_mois' => 'integer',
            'remise_pct' => 'decimal:2',
            'taxe_pct' => 'decimal:2',
            'line_total' => 'integer',
        ];
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }

    public function pig(): BelongsTo
    {
        return $this->belongsTo(Pig::class);
    }
}
