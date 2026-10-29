<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\BelongsToFarm;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class Pig extends Model
{
    use Auditable, BelongsToFarm, HasFactory;

    protected $fillable = [
        'batch_id', 'identifiant', 'nom', 'sexe', 'categorie',
        'date_naissance', 'date_arrivee', 'date_inconnue', 'poids_arrivee_kg',
        'prix_achat', 'valeur_reforme', 'provenance',
        'pere_id', 'mere_id', 'pere_nom', 'mere_nom', 'status', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'date_naissance' => 'date',
            'date_arrivee' => 'date',
            'date_inconnue' => 'boolean',
            'poids_arrivee_kg' => 'decimal:2',
            'prix_achat' => 'integer',
            'valeur_reforme' => 'integer',
        ];
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }

    public function pere(): BelongsTo
    {
        return $this->belongsTo(Pig::class, 'pere_id');
    }

    public function mere(): BelongsTo
    {
        return $this->belongsTo(Pig::class, 'mere_id');
    }

    public function weights(): HasMany
    {
        return $this->hasMany(WeightRecord::class);
    }

    // Age basis for the growth curve: birth date if known, else arrival date.
    public function ageReferenceDate(): ?Carbon
    {
        return $this->date_naissance ?? $this->date_arrivee;
    }
}
