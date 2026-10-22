<?php

namespace App\Http\Middleware;

use App\Services\Freight\FreightReminderService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\PhpExecutableFinder;

/**
 * Sends the daily freight reminders digest without a cron job, the same way
 * SyncFleetMileage keeps mileage current: the first signed-in request each
 * hour (from FREIGHT_REMINDERS_FROM_HOUR, default 07:00) for a freight
 * forwarding company claims a cache lock and starts `freight:send-reminders`
 * in the background. Each recipient is emailed at most once a day (enforced
 * in FreightReminderService), so the hourly re-check only picks up people
 * whose first reminder appeared later in the day, or whose send failed.
 */
class SendFreightReminders
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        try {
            $this->maybeSend($request);
        } catch (\Throwable $e) {
            Log::warning('Freight reminders trigger failed: ' . $e->getMessage());
        }

        return $response;
    }

    private function maybeSend(Request $request): void
    {
        $user = $request->user();
        if (! $user || ! config('services.freight_reminders.enabled', true)) {
            return;
        }
        if (now()->hour < (int) config('services.freight_reminders.from_hour', 7)) {
            return;
        }

        $company = $user->employee?->company ?? $user->company;
        if (! $company || ! $company->hasCompanyType('Freight Forwarding & Clearing')) {
            return;
        }

        if (! Cache::add('freight-reminders-run:' . $company->id, now()->toDateTimeString(), now()->addHour())) {
            return;
        }

        if (! function_exists('fastcgi_finish_request') && $this->launchInBackground($company->id)) {
            return;
        }

        app()->terminating(function () use ($company) {
            try {
                ignore_user_abort(true);
                @set_time_limit(300);
                app(FreightReminderService::class)->sendDailyDigests($company->id);
            } catch (\Throwable $e) {
                Log::warning('Freight reminders failed: ' . $e->getMessage());
            }
        });
    }

    /** Mirrors FleetMileageSyncService::launchInBackground (Apache mod_php can't run after the response). */
    private function launchInBackground(int $companyId): bool
    {
        $php = config('services.fleet_mileage_sync.php_binary') ?: (new PhpExecutableFinder)->find(false);
        if (! $php || ! preg_match('/php[\d.]*(\.exe)?$/i', basename($php))) {
            return false;
        }

        $command = escapeshellarg($php) . ' ' . escapeshellarg(base_path('artisan')) . ' freight:send-reminders --company=' . $companyId;

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
            Log::warning('Freight reminders: could not start background send: ' . $e->getMessage());
            return false;
        }

        return true;
    }
}
