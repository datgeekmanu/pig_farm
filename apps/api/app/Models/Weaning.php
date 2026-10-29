<?php

namespace App\Models;

use App\Models\Concerns\BelongsToFarm;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Weaning extends Model
{
    use BelongsToFarm, HasFactory;

    protected $fillable = ['farrowing_id', 'weaned_on', 'weaned_count', 'avg_weight_kg', 'notes'];

    protected function casts(): array
    {
        return [
            'weaned_on' => 'date',
            'weaned_count' => 'integer',
            'avg_weight_kg' => 'decimal:2',
        ];
    }

    public function farrowing(): BelongsTo
    {
        return $this->belongsTo(Farrowing::class);
    }
}
