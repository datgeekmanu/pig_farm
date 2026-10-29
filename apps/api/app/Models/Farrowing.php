<?php

namespace App\Models;

use App\Models\Concerns\BelongsToFarm;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Farrowing extends Model
{
    use BelongsToFarm, HasFactory;

    protected $fillable = ['breeding_id', 'sow_id', 'farrowed_on', 'born_alive', 'born_dead', 'mummies', 'notes'];

    protected function casts(): array
    {
        return [
            'farrowed_on' => 'date',
            'born_alive' => 'integer',
            'born_dead' => 'integer',
            'mummies' => 'integer',
        ];
    }

    public function breeding(): BelongsTo
    {
        return $this->belongsTo(Breeding::class);
    }

    public function sow(): BelongsTo
    {
        return $this->belongsTo(Pig::class, 'sow_id');
    }

    public function weaning(): HasOne
    {
        return $this->hasOne(Weaning::class);
    }
}
