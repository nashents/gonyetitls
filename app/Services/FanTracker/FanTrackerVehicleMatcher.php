<?php

namespace App\Services\FanTracker;

use App\Models\Horse;
use App\Models\IntegrationMapping;
use App\Models\Trailer;
use App\Models\Vehicle;
use App\Services\FanTracker\Concerns\ResolvesFanTrackerIntegration;
use Illuminate\Support\Collection;

/**
 * Links Gonyeti's Horse/Trailer/Vehicle rows to FanTracker trackers by
 * matching registration_number against the tracker's `label` field, caching
 * the tracker id via the generic IntegrationMapping table (entity_type:
 * horse_tracker / trailer_tracker / vehicle_tracker). Mirrors
 * App\Services\Cartrack\CartrackVehicleMatcher.
 *
 * NOTE: unlike Cartrack, FanTracker/Navixy has no dedicated
 * registration/plate field on a tracker — `label` is free text the fleet
 * operator sets when a device is installed. Live-checked against the test
 * account: the two trackers there are labelled "Phase 3-EMP3-820-4501" and
 * "vehicle_test" — neither looks like a registration number. Auto-matching
 * only works once trackers are (re)labelled to match Gonyeti's
 * registration_number values; unmatched rows are reported so that can be done.
 */
class FanTrackerVehicleMatcher
{
    use ResolvesFanTrackerIntegration;

    protected const ENTITY_MODELS = [
        'horse_tracker'   => Horse::class,
        'trailer_tracker' => Trailer::class,
        'vehicle_tracker' => Vehicle::class,
    ];

    public function matchForCompany(int $companyId): array
    {
        $integration = $this->activeFanTrackerIntegration($companyId);

        if (! $integration) {
            return ['success' => false, 'error' => 'FanTracker integration is not active for this company.'];
        }

        $result = $this->cachedTrackerList($integration);

        if (! ($result['success'] ?? false)) {
            return ['success' => false, 'error' => $result['error'] ?? 'Failed to list FanTracker trackers.'];
        }

        $trackers = $this->trackerRows($result)
            ->filter(fn ($t) => ! empty($t['label']))
            ->keyBy(fn ($t) => $this->normalize($t['label']));

        $matched   = [];
        $unmatched = [];

        foreach (self::ENTITY_MODELS as $entityType => $modelClass) {
            $modelClass::query()
                ->whereNotNull('registration_number')
                ->where('registration_number', '!=', '')
                ->chunkById(100, function (Collection $rows) use ($integration, $entityType, $trackers, &$matched, &$unmatched) {
                    foreach ($rows as $row) {
                        $key = $this->normalize($row->registration_number);
                        $tracker = $trackers->get($key);

                        if (! $tracker) {
                            $unmatched[] = ['entity_type' => $entityType, 'local_id' => $row->getKey(), 'registration_number' => $row->registration_number];
                            continue;
                        }

                        IntegrationMapping::updateOrCreate(
                            [
                                'company_integration_id' => $integration->id,
                                'entity_type'             => $entityType,
                                'local_id'                => $row->getKey(),
                            ],
                            [
                                'external_id'        => $tracker['id'],
                                'external_reference' => $tracker['label'],
                                'sync_status'         => IntegrationMapping::STATUS_SYNCED,
                                'last_synced_at'      => now(),
                                'last_attempted_at'   => now(),
                                'last_error'          => null,
                            ]
                        );

                        $matched[] = [
                            'entity_type'         => $entityType,
                            'local_id'            => $row->getKey(),
                            'registration_number' => $row->registration_number,
                            'fantracker_tracker_id' => $tracker['id'],
                        ];
                    }
                });
        }

        return ['success' => true, 'summary' => ['matched' => $matched, 'unmatched' => $unmatched]];
    }

    protected function normalize(string $label): string
    {
        return strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $label));
    }
}
