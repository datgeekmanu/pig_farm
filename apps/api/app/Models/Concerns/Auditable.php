<?php

namespace App\Models\Concerns;

use App\Models\AuditLog;
use App\Support\CurrentFarm;
use Illuminate\Database\Eloquent\Model;

// Records created/updated/deleted on the model into audit_logs (best-effort:
// captures the acting farm user and active farm when available).
trait Auditable
{
    public static function bootAuditable(): void
    {
        static::created(fn (Model $m) => self::writeAudit($m, 'created', $m->getChanges()));
        static::updated(fn (Model $m) => self::writeAudit($m, 'updated', $m->getChanges()));
        static::deleted(fn (Model $m) => self::writeAudit($m, 'deleted', null));
    }

    private static function writeAudit(Model $model, string $action, ?array $changes): void
    {
        $farm = app(CurrentFarm::class);

        AuditLog::create([
            'farm_id' => $farm->isSet() ? $farm->id() : null,
            'user_id' => auth('web')->id(),
            'auditable_type' => $model::class,
            'auditable_id' => $model->getKey(),
            'action' => $action,
            'changes' => $changes ? collect($changes)->except(['updated_at', 'created_at'])->all() ?: null : null,
            'created_at' => now(),
        ]);
    }
}
