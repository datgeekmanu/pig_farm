<?php

namespace App\Models;

use App\Models\Concerns\BelongsToFarm;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Batch extends Model
{
    use BelongsToFarm, HasFactory;

    protected $fillable = [
        'building_id', 'nom', 'batiment_loge', 'date_mise_en_lot', 'effectif_initial',
        'date_naissance', 'poids_moyen_initial_kg', 'prix_achat_unitaire',
        'status', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'date_mise_en_lot' => 'date',
            'date_naissance' => 'date',
            'effectif_initial' => 'integer',
            'poids_moyen_initial_kg' => 'decimal:2',
            'prix_achat_unitaire' => 'integer',
        ];
    }

    public function pigs(): HasMany
    {
        return $this->hasMany(Pig::class);
    }

    public function weightRecords(): HasMany
    {
        return $this->hasMany(WeightRecord::class);
    }

    public function building(): BelongsTo
    {
        return $this->belongsTo(Building::class);
    }

    public function mortalityRecords(): HasMany
    {
        return $this->hasMany(MortalityRecord::class);
    }

    public function saleItems(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function deathsCount(): int
    {
        return (int) $this->mortalityRecords()->sum('count');
    }

    public function soldCount(): int
    {
        return (int) $this->saleItems()->sum('quantite');
    }

    // Live head = initial minus recorded deaths and sales.
    public function currentHead(): int
    {
        return max(0, $this->effectif_initial - $this->deathsCount() - $this->soldCount());
    }
}
