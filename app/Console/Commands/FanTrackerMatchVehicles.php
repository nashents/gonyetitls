<?php

namespace App\Console\Commands;

use App\Models\CompanyIntegration;
use App\Services\FanTracker\FanTrackerVehicleMatcher;
use Illuminate\Console\Command;

class FanTrackerMatchVehicles extends Command
{
    protected $signature = 'fantracker:match-vehicles {company? : Company ID (defaults to every company with an active FanTracker integration)}';
    protected $description = 'Match Horses/Trailers/Vehicles to FanTracker trackers by registration number vs tracker label, and cache the link in integration_mappings.';

    public function handle(FanTrackerVehicleMatcher $matcher): int
    {
        $companyId = $this->argument('company');

        $companyIds = $companyId
            ? [(int) $companyId]
            : CompanyIntegration::whereHas('integration_provider', fn ($q) => $q->where('key', 'fantracker'))
                ->where('status', 'active')
                ->pluck('company_id')
                ->all();

        if (empty($companyIds)) {
            $this->warn('No active FanTracker integrations found.');
            return self::SUCCESS;
        }

        foreach ($companyIds as $id) {
            $result = $matcher->matchForCompany($id);

            if (! ($result['success'] ?? false)) {
                $this->error("Company {$id}: " . ($result['error'] ?? 'match failed'));
                continue;
            }

            $summary = $result['summary'];

            foreach ($summary['unmatched'] as $u) {
                $this->line("  - no FanTracker match for {$u['entity_type']} #{$u['local_id']} ({$u['registration_number']})");
            }

            foreach ($summary['matched'] as $m) {
                $this->line("  + matched {$m['entity_type']} #{$m['local_id']} ({$m['registration_number']}) -> FanTracker tracker {$m['fantracker_tracker_id']}");
            }

            $this->info("Company {$id}: matched " . count($summary['matched']) . ', unmatched ' . count($summary['unmatched']));
        }

        return self::SUCCESS;
    }
}
