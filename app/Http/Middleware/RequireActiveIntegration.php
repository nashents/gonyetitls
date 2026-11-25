<?php

namespace App\Http\Middleware;

use App\Services\Integrations\IntegrationGate;
use Closure;
use Illuminate\Http\Request;

/**
 * Integration-only pages 404 unless the acting user's company has the
 * integration ACTIVE — for everyone, admins included (they're exempt from the
 * module route guard, so menu visibility alone doesn't stop a typed URL).
 *
 *   ->middleware('integration:sage_intacct')    one provider key
 *   ->middleware('integration:type,tracking')   any active provider of a category
 */
class RequireActiveIntegration
{
    public function handle(Request $request, Closure $next, string $provider, ?string $type = null)
    {
        $active = $provider === 'type'
            ? IntegrationGate::enabledForUserType((string) $type)
            : IntegrationGate::enabledForUser($provider);

        abort_unless($active, 404);

        return $next($request);
    }
}
