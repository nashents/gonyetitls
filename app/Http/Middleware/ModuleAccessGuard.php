<?php

namespace App\Http\Middleware;

use App\Support\ModuleAccess;
use Closure;
use Illuminate\Http\Request;

/**
 * Server-side half of the per-user module locks (App\Support\ModuleAccess).
 * Hiding the sidebar entry is not enough — this stops a locked user opening the
 * module by URL.
 */
class ModuleAccessGuard
{
    public function handle(Request $request, Closure $next)
    {
        $name = $request->route()?->getName();

        if ($name && ModuleAccess::isBlockedRoute($name)) {
            abort(403, 'You do not have access to this module.');
        }

        return $next($request);
    }
}
