<?php

namespace App\Console\Commands;

use App\Models\CompanyIntegration;
use App\Services\Pinpoint\PinpointVehicleMatcher;
use Illuminate\Console\Command;

class PinpointMatchVehicles extends Command
{
    protected $signature = 'pinpoint:match-vehicles {company? : Company ID (defaults to every company with an active Pinpoint integration)}';
    protected $description = 'Match Horses/Trailers/Vehicles to Pinpoint trackers by registration number vs plate, and cache the link in integration_mappings.';

    public function handle(PinpointVehicleMatcher $matcher): int
    {
        $companyId = $this->argument('company');

        $companyIds = $companyId
            ? [(int) $companyId]
            : CompanyIntegration::whereHas('integration_provider', fn ($q) => $q->where('key', 'pinpoint'))
                ->where('status', 'active')
                ->pluck('company_id')
                ->all();

        if (empty($companyIds)) {
            $this->warn('No active Pinpoint integrations found.');
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
                $this->line("  - no Pinpoint match for {$u['entity_type']} #{$u['local_id']} ({$u['registration_number']})");
            }

            foreach ($summary['matched'] as $m) {
                $this->line("  + matched {$m['entity_type']} #{$m['local_id']} ({$m['registration_number']}) -> Pinpoint tracker {$m['pinpoint_uin']}");
            }

            $this->info("Company {$id}: matched " . count($summary['matched']) . ', unmatched ' . count($summary['unmatched']));
        }

        return self::SUCCESS;
    }
}
