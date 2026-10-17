<?php

namespace App\Models\Scopes;

use App\Support\CurrentFarm;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use RuntimeException;

// Fail-closed tenant isolation: every query on a BelongsToFarm model is filtered
// by the active farm. If no farm is set, we throw rather than leak cross-farm rows.
// Escape hatch for genuinely global queries (jobs, admin): Model::withoutFarmScope().
class FarmScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $farm = app(CurrentFarm::class);

        if (! $farm->isSet()) {
            throw new RuntimeException(
                'Query on ['.$model::class.'] without an active farm. '
                .'Set CurrentFarm or use withoutFarmScope() for a deliberate global query.'
            );
        }

        $builder->where($model->getTable().'.farm_id', $farm->id());
    }
}
