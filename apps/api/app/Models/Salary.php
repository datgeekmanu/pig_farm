<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\BelongsToFarm;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Salary extends Model
{
    use Auditable, BelongsToFarm, HasFactory;

    protected $fillable = ['nom_ouvrier', 'poste', 'montant', 'date_paiement', 'mois_concerne', 'notes'];

    protected function casts(): array
    {
        return ['date_paiement' => 'date', 'montant' => 'integer'];
    }
}
