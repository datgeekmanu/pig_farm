<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\BelongsToFarm;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Treatment extends Model
{
    use Auditable, BelongsToFarm, HasFactory;

    protected $fillable = [
        'scope', 'batch_id', 'pig_id', 'date', 'produit', 'dose', 'type', 'motif',
        'soignant', 'soignant_tel', 'cout', 'date_rappel', 'stock_item_id', 'quantite_utilisee', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'date_rappel' => 'date',
            'cout' => 'integer',
            'quantite_utilisee' => 'decimal:2',
        ];
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }

    public function pig(): BelongsTo
    {
        return $this->belongsTo(Pig::class);
    }

    public function stockItem(): BelongsTo
    {
        return $this->belongsTo(StockItem::class);
    }
}
