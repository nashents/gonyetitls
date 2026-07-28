<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Guards POST /api/webhooks/ezytrack. EzyTrack's Device Manager authenticates
 * with a single shared bearer token (see config/services.php ezytrack.token,
 * set via EZYTRACK_WEBHOOK_TOKEN) rather than per-company credentials, since
 * the feed is push-based and not scoped to a company at the transport layer.
 */
class VerifyEzyTrackToken
{
    public function handle(Request $request, Closure $next)
    {
        $expected = config('services.ezytrack.token');
        $provided = (string) $request->bearerToken();

        if (empty($expected) || $provided === '' || ! hash_equals((string) $expected, $provided)) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        return $next($request);
    }
}
