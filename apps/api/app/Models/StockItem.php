<?php

namespace App\Models;

use App\Models\Concerns\BelongsToFarm;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StockItem extends Model
{
    use BelongsToFarm, HasFactory;

    protected $fillable = ['type', 'nom', 'categorie', 'quantite', 'unite', 'seuil_alerte', 'prix_unitaire', 'date', 'notes'];

    protected function casts(): array
    {
        return [
            'quantite' => 'decimal:2',
            'seuil_alerte' => 'decimal:2',
            'prix_unitaire' => 'integer',
            'date' => 'date',
        ];
    }

    public function movements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    protected function isLow(): Attribute
    {
        return Attribute::get(
            fn () => $this->seuil_alerte !== null && (float) $this->quantite <= (float) $this->seuil_alerte
        );
    }
}
