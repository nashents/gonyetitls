<?php

namespace App\Services\Cartrack;

use App\Models\Horse;
use App\Models\IntegrationMapping;
use App\Models\Trailer;
use App\Models\Vehicle;
use App\Services\Cartrack\Concerns\ResolvesCartrackIntegration;
use Illuminate\Support\Collection;

/**
 * Links Gonyeti's Horse/Trailer/Vehicle rows to Cartrack vehicles by matching
 * registration_number against Cartrack's `registration` field, caching the
 * Cartrack vehicle id via the generic IntegrationMapping table (entity_type:
 * horse_vehicle / trailer_vehicle / vehicle_vehicle).
 *
 * NOTE: the exact `/vehicles` response shape wasn't fully available from the
 * API docs at build time — extractRows() defensively handles a few common
 * wrapper shapes ([data] / [data][data] / a bare list).
 * Confirm against a live response and adjust if Cartrack returns something else.
 */
class CartrackVehicleMatcher
{
    use ResolvesCartrackIntegration;

    protected const ENTITY_MODELS = [
        'horse_vehicle'   => Horse::class,
        'trailer_vehicle' => Trailer::class,
        'vehicle_vehicle' => Vehicle::class,
    ];

    public function matchForCompany(int $companyId): array
    {
        $integration = $this->activeCartrackIntegration($companyId);

        if (! $integration) {
            return ['success' => false, 'error' => 'Cartrack integration is not active for this company.'];
        }

        $driver = $this->cartrackDriverFor($integration);
        $result = $driver->listVehicles();

        if (! $result['success']) {
            return ['success' => false, 'error' => $result['error'] ?? 'Failed to list Cartrack vehicles.'];
        }

        $cartrackVehicles = $this->extractRows($result['data'])
            ->filter(fn ($v) => ! empty($v['registration']))
            ->keyBy(fn ($v) => $this->normalize($v['registration']));

        $matched   = [];
        $unmatched = [];

        foreach (self::ENTITY_MODELS as $entityType => $modelClass) {
            $modelClass::query()
                ->whereNotNull('registration_number')
                ->where('registration_number', '!=', '')
                ->chunkById(100, function (Collection $rows) use ($integration, $entityType, $cartrackVehicles, &$matched, &$unmatched) {
                    foreach ($rows as $row) {
                        $key = $this->normalize($row->registration_number);
                        $cartrackVehicle = $cartrackVehicles->get($key);

                        if (! $cartrackVehicle) {
                            $unmatched[] = ['entity_type' => $entityType, 'local_id' => $row->getKey(), 'registration_number' => $row->registration_number];
                            continue;
                        }

                        $externalId = (string) ($cartrackVehicle['vehicle_id'] ?? $cartrackVehicle['registration']);

                        IntegrationMapping::updateOrCreate(
                            [
                                'company_integration_id' => $integration->id,
                                'entity_type'             => $entityType,
                                'local_id'                => $row->getKey(),
                            ],
                            [
                                'external_id'        => $externalId,
                                'external_reference' => $cartrackVehicle['registration'],
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
                            'cartrack_vehicle_id' => $externalId,
                        ];
                    }
                });
        }

        return ['success' => true, 'summary' => ['matched' => $matched, 'unmatched' => $unmatched]];
    }

    /** Defensively unwrap whatever shape the Cartrack list response comes back in. */
    protected function extractRows($data): Collection
    {
        if (! is_array($data)) {
            return collect();
        }

        foreach ([$data, $data['data'] ?? null, $data['data']['data'] ?? null, $data['results'] ?? null] as $candidate) {
            if (is_array($candidate) && array_is_list($candidate)) {
                return collect($candidate);
            }
        }

        return collect();
    }

    protected function normalize(string $registration): string
    {
        return strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $registration));
    }
}
