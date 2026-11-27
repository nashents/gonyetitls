<?php

namespace App\Http\Middleware;

use App\Integrations\GonyetiLink\GonyetiLinkDriver;
use App\Services\GonyetiLink\GonyetiLinkService;
use Closure;
use Illuminate\Http\Request;

/**
 * Guards POST /api/gonyeti-link. The sender signs "{timestamp}.{body}" with the
 * shared secret of its Gonyeti Instance Link integration. The matching active
 * integration here (same secret) identifies the company and is handed to the
 * controller as the "gonyeti_link_integration" request attribute.
 */
class VerifyGonyetiLinkSignature
{
    public function handle(Request $request, Closure $next)
    {
        $timestamp = (string) $request->header('X-Gonyeti-Timestamp', '');
        $signature = (string) $request->header('X-Gonyeti-Signature', '');

        if ($timestamp === '' || $signature === '' || !ctype_digit($timestamp)
            || abs(time() - (int) $timestamp) > GonyetiLinkDriver::MAX_CLOCK_SKEW) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $body = $request->getContent();

        foreach (app(GonyetiLinkService::class)->activeIntegrations() as $integration) {
            $secret = (string) ($integration->credentials['shared_secret'] ?? '');
            if ($secret !== '' && hash_equals(GonyetiLinkDriver::sign($secret, $timestamp, $body), $signature)) {
                $request->attributes->set('gonyeti_link_integration', $integration);

                return $next($request);
            }
        }

        return response()->json(['message' => 'Unauthorized'], 401);
    }
}
