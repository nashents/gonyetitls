<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Services\Fleet\FleetMileageSyncService;

/**
 * Keeps fleet mileage in step with the tracking integrations without a cron
 * job: the first signed-in request in each interval (default 10 min) claims a
 * per-company cache lock and starts the sync without making the user wait.
 *
 *  - PHP-FPM: Laravel flushes the response (fastcgi_finish_request) before
 *    terminating callbacks run, so the sync runs in-process after the page loads.
 *  - Apache mod_php (e.g. Laragon) has no equivalent, so the sync is started as
 *    a detached `php artisan fleet:sync-mileage` process instead.
 *  - If neither is possible it runs in-process (that one request waits).
 */
class SyncFleetMileage
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        $user = $request->user();
        if (! $user) {
            return $response;
        }

        $companyId = optional($user->employee)->company_id ?? $user->company_id ?? null;
        $service = app(FleetMileageSyncService::class);

        if (! $service->claim($companyId)) {
            return $response;
        }

        if (! function_exists('fastcgi_finish_request') && $service->launchInBackground($user->id)) {
            return $response;
        }

        app()->terminating(function () use ($service, $companyId) {
            try {
                if (! $service->hasActiveTracking($companyId)) {
                    return;
                }
                ignore_user_abort(true);
                @set_time_limit(300);
                $result = $service->sync();
                if ($result['updated'] > 0) {
                    Log::info("FleetMileageSync: updated {$result['updated']} of {$result['checked']} tracked assets.");
                }
            } catch (\Throwable $e) {
                Log::warning('FleetMileageSync failed: ' . $e->getMessage());
            }
        });

        return $response;
    }
}
