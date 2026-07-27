<?php

namespace App\Console\Commands;

use App\Models\CompanyIntegration;
use App\Services\Cartrack\CartrackVehicleMatcher;
use Illuminate\Console\Command;

class CartrackMatchVehicles extends Command
{
    protected $signature = 'cartrack:match-vehicles {company? : Company ID (defaults to every company with an active Cartrack integration)}';
    protected $description = 'Match Horses/Trailers/Vehicles to Cartrack vehicles by registration number and cache the link in integration_mappings.';

    public function handle(CartrackVehicleMatcher $matcher): int
    {
        $companyId = $this->argument('company');

        $companyIds = $companyId
            ? [(int) $companyId]
            : CompanyIntegration::whereHas('integration_provider', fn ($q) => $q->where('key', 'cartrack'))
                ->where('status', 'active')
                ->pluck('company_id')
                ->all();

        if (empty($companyIds)) {
            $this->warn('No active Cartrack integrations found.');
            return self::SUCCESS;
        }

        foreach ($companyIds as $id) {
            $result = $matcher->matchForCompany($id);

            if (! ($result['success'] ?? false)) {
                $this->error("Company {$id}: " . ($result['error'] ?? 'match failed'));
                continue;
            }

            $summary = $result['summary'];

            // Unmatched prints first and matched last — with 70+ fleet records
            // the unmatched list alone can overflow a terminal window, so the
            // matched confirmation (the part you actually need to see) stays
            // visible at the bottom instead of scrolling off-screen.
            foreach ($summary['unmatched'] as $u) {
                $this->line("  - no Cartrack match for {$u['entity_type']} #{$u['local_id']} ({$u['registration_number']})");
            }

            foreach ($summary['matched'] as $m) {
                $this->line("  + matched {$m['entity_type']} #{$m['local_id']} ({$m['registration_number']}) -> Cartrack vehicle {$m['cartrack_vehicle_id']}");
            }

            $this->info("Company {$id}: matched " . count($summary['matched']) . ', unmatched ' . count($summary['unmatched']));
        }

        return self::SUCCESS;
    }
}
