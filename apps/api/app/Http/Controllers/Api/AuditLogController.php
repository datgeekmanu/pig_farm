<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Support\CurrentFarm;
use Illuminate\Http\JsonResponse;

class AuditLogController extends Controller
{
    public function index(): JsonResponse
    {
        $logs = AuditLog::where('farm_id', app(CurrentFarm::class)->id())
            ->latest('created_at')
            ->paginate(50)
            ->through(fn (AuditLog $l) => [
                'id' => $l->id,
                'user_id' => $l->user_id,
                'model' => class_basename($l->auditable_type),
                'auditable_id' => $l->auditable_id,
                'action' => $l->action,
                'changes' => $l->changes,
                'created_at' => $l->created_at,
            ]);

        return response()->json($logs);
    }
}
