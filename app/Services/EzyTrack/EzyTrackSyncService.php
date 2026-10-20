<?php

namespace App\Services\EzyTrack;

use App\Models\CompanyIntegration;
use App\Models\EzyTrackDevice;
use App\Models\Horse;
use App\Models\IntegrationMapping;
use App\Models\Trailer;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Model;

/**
 * Live odometer / engine hours for EzyTrack-tracked units. EzyTrack pushes to
 * us (see EzyTrackPositionIngestor), so unlike the Cartrack/FanTracker/Pinpoint
 * sync services there is no API call here — readings come from the locally
 * stored ezytrack_devices row of the device mapped to the unit.
 *
 * Odometer and run hours only arrive when the device sends Digital Matter
 * FType 27 (Device Odometer and Run Hours); devices that don't simply return
 * no mileage, and callers fall back to the unit's stored value.
 */
class EzyTrackSyncService
{
    /** integration_mappings.entity_type -> fleet model. */
    protected const ENTITY_MODELS = [
        'horse_ezytrack_device'   => Horse::class,
        'trailer_ezytrack_device' => Trailer::class,
        'vehicle_ezytrack_device' => Vehicle::class,
    ];

    /** @var array<int, bool> company_integration_id => active */
    protected $activeIntegrations = [];

    /**
     * Same shape as the other providers' currentSnapshot(): ['mileage', 'hours',
     * 'latitude', 'longitude', 'last_update', 'ignition_on'], or null when the
     * unit has no active EzyTrack mapping / the device hasn't reported yet.
     * Callers must fall back to the model's own stored `mileage` on null.
     */
    public function currentSnapshot(Model $model): ?array
    {
        $mapping = $model->ezyTrackMapping;

        if (! $mapping || empty($mapping->external_reference) || ! $this->integrationIsActive($mapping->company_integration_id)) {
            return null;
        }

        $device = EzyTrackDevice::where('serial_number', $mapping->external_reference)->first();

        if (! $device) {
            return null;
        }

        return [
            'mileage'     => $device->odometer_km !== null ? (float) $device->odometer_km : null,
            'hours'       => $device->run_hours !== null ? (float) $device->run_hours : null,
            'latitude'    => $device->latitude,
            'longitude'   => $device->longitude,
            'last_update' => optional($device->odometer_at ?: $device->last_gps_at)->toDateTimeString(),
            'ignition_on' => $device->ignition_on,
        ];
    }

    /**
     * Copies the device's odometer and run hours onto every unit it is mapped to,
     * as each push arrives. Increase-only, matching FleetMileageSyncService: a
     * lower reading (tracker swapped, odometer never set on the device) is ignored.
     */
    public function applyReadingsToMappedAssets(EzyTrackDevice $device): int
    {
        $mappings = IntegrationMapping::whereIn('entity_type', array_keys(self::ENTITY_MODELS))
            ->where('external_reference', $device->serial_number)
            ->get();

        $updated = 0;

        foreach ($mappings as $mapping) {
            if (! $this->integrationIsActive($mapping->company_integration_id)) {
                continue;
            }

            $modelClass = self::ENTITY_MODELS[$mapping->entity_type];
            $asset = $modelClass::find($mapping->local_id);

            if (! $asset) {
                continue;
            }

            $changes = [];

            if ($device->odometer_km !== null && (float) $device->odometer_km > 0
                && (float) $device->odometer_km >= (float) $asset->mileage + 1) {
                $changes['mileage'] = round((float) $device->odometer_km);
            }

            if ($device->run_hours !== null && (float) $device->run_hours > 0
                && (float) $device->run_hours > (float) $asset->hours) {
                $changes['hours'] = round((float) $device->run_hours, 1);
            }

            if ($changes) {
                // Query-level update: skips model events/audits, which would otherwise log a row on every push.
                $asset->newQuery()->whereKey($asset->getKey())->update($changes);
                $updated++;
            }
        }

        return $updated;
    }

    protected function integrationIsActive($companyIntegrationId): bool
    {
        if (! $companyIntegrationId) {
            return false;
        }

        if (! array_key_exists($companyIntegrationId, $this->activeIntegrations)) {
            $this->activeIntegrations[$companyIntegrationId] = CompanyIntegration::whereKey($companyIntegrationId)
                ->where('status', 'active')
                ->exists();
        }

        return $this->activeIntegrations[$companyIntegrationId];
    }
}
