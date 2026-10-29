<?php

namespace App\Models;

use App\Models\Concerns\BelongsToFarm;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Building extends Model
{
    use BelongsToFarm, HasFactory;

    protected $fillable = ['nom', 'surface_m2', 'notes'];

    protected function casts(): array
    {
        return [
            'surface_m2' => 'decimal:2',
        ];
    }

    public function batches(): HasMany
    {
        return $this->hasMany(Batch::class);
    }
}
