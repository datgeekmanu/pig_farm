<?php

namespace App\Models;

use App\Models\Concerns\BelongsToFarm;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Formula extends Model
{
    use BelongsToFarm, HasFactory;

    protected $fillable = ['nom', 'phase', 'mode', 'poids_total_kg', 'cost_per_kg'];

    protected function casts(): array
    {
        return [
            'poids_total_kg' => 'decimal:2',
            'cost_per_kg' => 'integer',
        ];
    }

    public function lines(): HasMany
    {
        return $this->hasMany(FormulaLine::class);
    }
}
