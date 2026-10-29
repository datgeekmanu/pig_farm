<?php

namespace App\Models;

use App\Models\Concerns\BelongsToFarm;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Ingredient extends Model
{
    use BelongsToFarm, HasFactory;

    protected $fillable = ['nom', 'prix_kg'];

    protected function casts(): array
    {
        return ['prix_kg' => 'integer'];
    }

    public function nutrients(): BelongsToMany
    {
        return $this->belongsToMany(Nutrient::class)
            ->withPivot('value')
            ->withTimestamps();
    }

    /**
     * Nutrient content keyed by nutrient code (per kg).
     *
     * @return array<string, float>
     */
    public function nutrientMap(): array
    {
        return $this->nutrients->mapWithKeys(
            fn (Nutrient $n) => [$n->code => (float) $n->pivot->value]
        )->all();
    }
}
