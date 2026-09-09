<?php

namespace App\Console\Commands;

use App\Models\AssetPositionLog;
use App\Models\CompanyIntegration;
use App\Models\IntegrationProvider;
use App\Models\Trip;
use App\Services\Fleet\FleetPositionResolver;
use Illuminate\Console\Command;

class LogAssetPositions extends Command
{
    protected $signature = 'fleet:log-asset-positions {company? : Company ID (defaults to every company with an active tracking integration)}';
    protected $description = 'Snapshot every tracked truck/vehicle\'s current position into asset_position_logs, for the Asset Positions dwell-time/distance columns and trip route history.';

    /** asset_type => [FK column on asset_position_logs, FK column on trips]. */
    protected const ASSET_COLUMNS = [
        'horse'   => ['horse_id', 'horse_id'],
        'vehicle' => ['vehicle_id', 'vehicle_id'],
    ];

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
            $count = 0;

            foreach (self::ASSET_COLUMNS as $assetType => [$logColumn, $tripColumn]) {
                $typePositions = $positions->filter(fn ($p) => $p['asset_type'] === $assetType);

                // Batch-resolve each of these assets' currently active trip
                // (if any) in one query, rather than one query per position.
                $activeTripByAssetId = Trip::whereIn($tripColumn, $typePositions->pluck('local_id'))
                    ->whereIn('trip_status', Trip::ACTIVE_TRACKING_STATUSES)
                    ->get(['id', $tripColumn])
                    ->keyBy($tripColumn);

                foreach ($typePositions as $position) {
                    AssetPositionLog::create([
                        $logColumn    => $position['local_id'],
                        'trip_id'     => optional($activeTripByAssetId->get($position['local_id']))->id,
                        'company_id'  => $id,
                        'source'      => $position['source'],
                        'latitude'    => $position['lat'],
                        'longitude'   => $position['lng'],
                        'speed'       => $position['speed'],
                        'odometer'    => $position['odometer'] ?? null,
                        'recorded_at' => $now,
                    ]);
                    $count++;
                }
            }

            $logged += $count;
            $this->line("Company {$id}: logged {$count} position(s).");
        }

        $this->info("Logged {$logged} asset position(s) total.");

        return self::SUCCESS;
    }
}
