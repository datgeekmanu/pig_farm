<?php

namespace App\Models;

use App\Models\Concerns\BelongsToFarm;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeedIntake extends Model
{
    use BelongsToFarm, HasFactory;

    protected $fillable = ['batch_id', 'stock_item_id', 'date', 'quantity_kg', 'cost', 'notes'];

    protected function casts(): array
    {
        return ['date' => 'date', 'quantity_kg' => 'decimal:2', 'cost' => 'integer'];
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }
}
