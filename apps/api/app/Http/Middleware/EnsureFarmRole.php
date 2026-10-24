<?php

namespace App\Http\Middleware;

use App\Support\CurrentFarm;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

// Gate a route by the user's role on the active farm, e.g. `role:owner`.
// Must run after auth:sanctum + farm (needs user + active farm).
class EnsureFarmRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $farm = app(CurrentFarm::class);

        if (! $farm->isSet() || ! in_array($request->user()?->roleOn($farm->id()), $roles, true)) {
            abort(403, 'Insufficient role for this farm.');
        }

        return $next($request);
    }
}
