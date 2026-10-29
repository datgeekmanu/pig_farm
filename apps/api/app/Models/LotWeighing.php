<?php

namespace App\Models;

use App\Models\Concerns\BelongsToFarm;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LotWeighing extends Model
{
    use BelongsToFarm, HasFactory;

    protected $fillable = ['batch_id', 'weighed_on', 'count', 'avg_weight_kg', 'uniformity_cv', 'weights', 'notes'];

    protected function casts(): array
    {
        return [
            'weighed_on' => 'date',
            'count' => 'integer',
            'avg_weight_kg' => 'decimal:2',
            'uniformity_cv' => 'decimal:2',
            'weights' => 'array',
        ];
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }
}
