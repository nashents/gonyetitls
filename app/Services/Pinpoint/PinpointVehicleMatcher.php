<?php

namespace App\Services\Pinpoint;

use App\Models\Horse;
use App\Models\IntegrationMapping;
use App\Models\Trailer;
use App\Models\Vehicle;
use App\Services\Pinpoint\Concerns\ResolvesPinpointIntegration;
use Illuminate\Support\Collection;

/**
 * Links Gonyeti's Horse/Trailer/Vehicle rows to Pinpoint trackers by matching
 * registration_number against Pinpoint's registration, caching the tracker's
 * `uin` via the generic IntegrationMapping table (entity_type:
 * horse_pinpoint / trailer_pinpoint / vehicle_pinpoint).
 *
 * Confirmed against a live GET /api2/trackers response on 2026-09-01: `plate`
 * comes back empty for every tracker on the account, but `name` holds the
 * actual registration (e.g. "AHA 2872") — so match on plate when present,
 * falling back to name.
 */
class PinpointVehicleMatcher
{
    use ResolvesPinpointIntegration;

    protected const ENTITY_MODELS = [
        'horse_pinpoint'   => Horse::class,
        'trailer_pinpoint' => Trailer::class,
        'vehicle_pinpoint' => Vehicle::class,
    ];

    public function matchForCompany(int $companyId): array
    {
        $integration = $this->activePinpointIntegration($companyId);

        if (! $integration) {
            return ['success' => false, 'error' => 'Pinpoint integration is not active for this company.'];
        }

        $driver = $this->pinpointDriverFor($integration);
        $result = $driver->listTrackers();

        if (! $result['success']) {
            return ['success' => false, 'error' => $result['error'] ?? 'Failed to list Pinpoint trackers.'];
        }

        $pinpointTrackers = collect(is_array($result['data']) ? $result['data'] : [])
            ->map(function ($t) {
                $t['registration'] = $t['plate'] ?: ($t['name'] ?? '');
                return $t;
            })
            ->filter(fn ($t) => ! empty($t['registration']))
            ->keyBy(fn ($t) => $this->normalize($t['registration']));

        $matched   = [];
        $unmatched = [];

        foreach (self::ENTITY_MODELS as $entityType => $modelClass) {
            $modelClass::query()
                ->whereNotNull('registration_number')
                ->where('registration_number', '!=', '')
                ->chunkById(100, function (Collection $rows) use ($integration, $entityType, $pinpointTrackers, &$matched, &$unmatched) {
                    foreach ($rows as $row) {
                        $key = $this->normalize($row->registration_number);
                        $tracker = $pinpointTrackers->get($key);

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
                                'external_id'        => (string) $tracker['uin'],
                                'external_reference' => $tracker['registration'],
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
                            'pinpoint_uin'        => (string) $tracker['uin'],
                        ];
                    }
                });
        }

        return ['success' => true, 'summary' => ['matched' => $matched, 'unmatched' => $unmatched]];
    }

    protected function normalize(string $plate): string
    {
        return strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $plate));
    }
}
