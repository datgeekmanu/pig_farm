<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PhaseRequirement extends Model
{
    protected $fillable = ['phase', 'nutrient_id', 'min_value', 'max_value'];

    protected function casts(): array
    {
        return ['min_value' => 'decimal:3', 'max_value' => 'decimal:3'];
    }

    public function nutrient(): BelongsTo
    {
        return $this->belongsTo(Nutrient::class);
    }
}
