<?php

namespace App\Http\Middleware;

use App\Services\Billing\SubscriptionBillingService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\PhpExecutableFinder;

/**
 * Runs Gonyeti's subscription billing without a cron job, the same way
 * SendFreightReminders does: the first signed-in request each hour (from
 * SUBSCRIPTION_BILLING_FROM_HOUR, default 08:00) claims a cache lock and
 * starts `billing:run` in the background. The run itself is idempotent - one
 * invoice per client per month, one delivery, reminders paced per client - so
 * the hourly re-check only picks up what is newly due or failed earlier.
 */
class RunSubscriptionBilling
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        try {
            $this->maybeRun($request);
        } catch (\Throwable $e) {
            Log::warning('Subscription billing trigger failed: ' . $e->getMessage());
        }

        return $response;
    }

    private function maybeRun(Request $request): void
    {
        if (! $request->user() || ! config('services.subscription_billing.enabled', true)) {
            return;
        }
        if (now()->hour < (int) config('services.subscription_billing.from_hour', 8)) {
            return;
        }
        if (! Cache::add('subscription-billing-trigger', now()->toDateTimeString(), now()->addHour())) {
            return;
        }

        if (! function_exists('fastcgi_finish_request') && $this->launchInBackground()) {
            return;
        }

        app()->terminating(function () {
            try {
                ignore_user_abort(true);
                @set_time_limit(300);
                app(SubscriptionBillingService::class)->runDaily();
            } catch (\Throwable $e) {
                Log::warning('Subscription billing run failed: ' . $e->getMessage());
            }
        });
    }

    /** Mirrors FleetMileageSyncService::launchInBackground (Apache mod_php can't run after the response). */
    private function launchInBackground(): bool
    {
        $php = config('services.fleet_mileage_sync.php_binary') ?: (new PhpExecutableFinder)->find(false);
        if (! $php || ! preg_match('/php[\d.]*(\.exe)?$/i', basename($php))) {
            return false;
        }

        $command = escapeshellarg($php) . ' ' . escapeshellarg(base_path('artisan')) . ' billing:run';

        try {
            if (PHP_OS_FAMILY === 'Windows') {
                if (! function_exists('popen') || ! function_exists('pclose')) {
                    return false;
                }
                pclose(popen('start "" /B ' . $command . ' > NUL 2>&1', 'r'));
            } else {
                if (! function_exists('exec')) {
                    return false;
                }
                exec($command . ' > /dev/null 2>&1 &');
            }
        } catch (\Throwable $e) {
            Log::warning('Subscription billing: could not start background run: ' . $e->getMessage());
            return false;
        }

        return true;
    }
}
