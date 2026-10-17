<?php

namespace App\Http\Middleware;

use App\Support\CurrentFarm;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

// Resolves the active farm from the X-Farm-Id header, verifying the authenticated
// user actually belongs to it. Falls back to the user's first farm when no header
// is sent (single-farm customers never need to pick). Apply after auth:sanctum.
class ResolveFarm
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user) {
            $requested = $request->header('X-Farm-Id');

            $farm = $requested
                ? $user->farms()->whereKey($requested)->first()
                : $user->farms()->first();

            if ($requested && ! $farm) {
                abort(403, 'You do not have access to this farm.');
            }

            if ($farm) {
                app(CurrentFarm::class)->set($farm->id);
            }
        }

        return $next($request);
    }
}
