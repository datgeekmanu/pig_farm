<?php

namespace App\Models;

use App\Models\Concerns\BelongsToFarm;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Breeding extends Model
{
    use BelongsToFarm, HasFactory;

    protected $fillable = [
        'sow_id', 'boar_id', 'band_id', 'bred_on', 'method', 'expected_farrow_on',
        'ultrasound_on', 'ultrasound_result', 'heat_return_on', 'status', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'bred_on' => 'date',
            'expected_farrow_on' => 'date',
            'ultrasound_on' => 'date',
            'heat_return_on' => 'date',
        ];
    }

    public function sow(): BelongsTo
    {
        return $this->belongsTo(Pig::class, 'sow_id');
    }

    public function boar(): BelongsTo
    {
        return $this->belongsTo(Pig::class, 'boar_id');
    }

    public function band(): BelongsTo
    {
        return $this->belongsTo(Band::class);
    }

    public function farrowing(): HasOne
    {
        return $this->hasOne(Farrowing::class);
    }
}
