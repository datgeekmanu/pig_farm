<?php

namespace App\Models;

use App\Models\Concerns\BelongsToFarm;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Band extends Model
{
    use BelongsToFarm, HasFactory;

    protected $fillable = ['nom', 'taille_objectif', 'date_prevue_saillie', 'notes'];

    protected function casts(): array
    {
        return ['date_prevue_saillie' => 'date', 'taille_objectif' => 'integer'];
    }

    public function breedings(): HasMany
    {
        return $this->hasMany(Breeding::class);
    }
}
