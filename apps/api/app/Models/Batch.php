<?php

namespace App\Models;

use App\Models\Concerns\BelongsToFarm;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Batch extends Model
{
    use BelongsToFarm, HasFactory;

    protected $fillable = [
        'nom', 'batiment_loge', 'date_mise_en_lot', 'effectif_initial',
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
}
