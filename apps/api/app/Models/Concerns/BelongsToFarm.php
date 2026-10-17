<?php

namespace App\Models\Concerns;

use App\Models\Farm;
use App\Models\Scopes\FarmScope;
use App\Support\CurrentFarm;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// Apply to every tenant-owned model. Adds the FarmScope global filter and
// auto-stamps farm_id on create from the active farm.
trait BelongsToFarm
{
    public static function bootBelongsToFarm(): void
    {
        static::addGlobalScope(new FarmScope);

        static::creating(function ($model) {
            if ($model->farm_id === null) {
                $model->farm_id = app(CurrentFarm::class)->require();
            }
        });
    }

    public function farm(): BelongsTo
    {
        return $this->belongsTo(Farm::class);
    }
}
