<?php

namespace App\Models;

use App\Models\Concerns\BelongsToFarm;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WeightRecord extends Model
{
    use BelongsToFarm, HasFactory;

    protected $fillable = ['pig_id', 'batch_id', 'weight_kg', 'weighed_on', 'notes'];

    protected function casts(): array
    {
        return [
            'weighed_on' => 'date',
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
