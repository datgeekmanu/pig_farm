<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    protected $fillable = ['order_id', 'product_id', 'nom', 'unit', 'prix', 'quantity', 'line_total'];

    protected function casts(): array
    {
        return ['prix' => 'integer', 'quantity' => 'decimal:2', 'line_total' => 'integer'];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
