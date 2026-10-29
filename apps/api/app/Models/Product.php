<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// Not globally farm-scoped: the public catalog reads across farms. Seller
// endpoints filter by the active farm explicitly.
class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'farm_id', 'batch_id', 'type', 'category', 'nom', 'description', 'unit',
        'prix', 'market_price', 'stock_qty', 'is_published', 'visible', 'photo_path',
    ];

    protected function casts(): array
    {
        return [
            'prix' => 'integer',
            'market_price' => 'integer',
            'stock_qty' => 'decimal:2',
            'is_published' => 'boolean',
            'visible' => 'boolean',
        ];
    }

    public function farm(): BelongsTo
    {
        return $this->belongsTo(Farm::class);
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }
}
