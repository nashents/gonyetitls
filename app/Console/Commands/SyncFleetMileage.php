<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\Fleet\FleetMileageSyncService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class SyncFleetMileage extends Command
{
    protected $signature = 'fleet:sync-mileage {--user= : Run as this user, so assets without a transporter resolve to their company (the web trigger passes this)}';
    protected $description = 'Pull live odometer readings from the tracking integrations into horse/trailer/vehicle mileage now (normally triggered automatically by web traffic).';

    public function handle(FleetMileageSyncService $service): int
    {
        if ($userId = $this->option('user')) {
            $user = User::with('employee')->find($userId);
            if (! $user) {
                $this->error("User #{$userId} not found.");
                return self::FAILURE;
            }
            // Tracking providers fall back to the signed-in user's company when an asset has no transporter.
            Auth::setUser($user);

            $companyId = optional($user->employee)->company_id ?? $user->company_id ?? null;
            if (! $service->hasActiveTracking($companyId)) {
                $this->info('No active tracking integration for this company.');
                return self::SUCCESS;
            }
        }

        $result = $service->sync();
        Log::debug("FleetMileageSync: checked {$result['checked']}, updated {$result['updated']}.");
        $this->info("Checked {$result['checked']} tracked asset(s), updated {$result['updated']}.");

        return self::SUCCESS;
    }
}
