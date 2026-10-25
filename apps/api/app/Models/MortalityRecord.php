<?php

namespace App\Models;

use App\Models\Concerns\BelongsToFarm;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MortalityRecord extends Model
{
    use BelongsToFarm, HasFactory;

    protected $fillable = ['pig_id', 'batch_id', 'died_on', 'cause', 'count', 'weight_kg', 'notes', 'client_uuid'];

    protected function casts(): array
    {
        return [
            'died_on' => 'date',
            'count' => 'integer',
            'weight_kg' => 'decimal:2',
        ];
    }

    public function pig(): BelongsTo
    {
        return $this->belongsTo(Pig::class);
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }
}
