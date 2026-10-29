<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FormulaLine extends Model
{
    protected $fillable = ['formula_id', 'ingredient_id', 'percent'];

    protected function casts(): array
    {
        return ['percent' => 'decimal:3'];
    }

    public function ingredient(): BelongsTo
    {
        return $this->belongsTo(Ingredient::class);
    }
}
