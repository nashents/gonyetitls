<?php

namespace App\Console\Commands;

use App\Models\AssetPositionLog;
use App\Models\CompanyIntegration;
use App\Models\IntegrationProvider;
use App\Services\Fleet\FleetPositionResolver;
use Illuminate\Console\Command;

class LogAssetPositions extends Command
{
    protected $signature = 'fleet:log-asset-positions {company? : Company ID (defaults to every company with an active tracking integration)}';
    protected $description = 'Snapshot every tracked truck\'s current position into asset_position_logs, for the Asset Positions dwell-time/distance columns.';

    public function handle(FleetPositionResolver $resolver): int
    {
        $companyId = $this->argument('company');

        $companyIds = $companyId
            ? [(int) $companyId]
            : CompanyIntegration::whereHas('integration_provider', fn ($q) => $q->where('type', 'tracking'))
                ->where('status', 'active')
                ->distinct()
                ->pluck('company_id')
                ->all();

        if (empty($companyIds)) {
            $this->warn('No active tracking integrations found.');
            return self::SUCCESS;
        }

        $now = now();
        $logged = 0;

        foreach ($companyIds as $id) {
            $positions = $resolver->resolve($id);

            // asset_position_logs only has a horse_id column today — vehicle
            // dwell-time/distance isn't tracked yet, only their live Position.
            $horsePositions = $positions->filter(fn ($p) => $p['asset_type'] === 'horse');

            foreach ($horsePositions as $position) {
                AssetPositionLog::create([
                    'horse_id'    => $position['local_id'],
                    'company_id'  => $id,
                    'source'      => $position['source'],
                    'latitude'    => $position['lat'],
                    'longitude'   => $position['lng'],
                    'speed'       => $position['speed'],
                    'recorded_at' => $now,
                ]);
                $logged++;
            }

            $this->line("Company {$id}: logged " . $horsePositions->count() . ' position(s).');
        }

        $this->info("Logged {$logged} asset position(s) total.");

        return self::SUCCESS;
    }
}
