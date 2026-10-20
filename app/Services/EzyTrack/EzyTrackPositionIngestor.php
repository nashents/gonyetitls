<?php

namespace App\Services\EzyTrack;

use App\Models\EzyTrackDevice;
use App\Models\IntegrationLog;
use App\Models\IntegrationMapping;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Parses one Digital Matter "base packet" (SerNo/IMEI/ICCID/ProdId/FW +
 * Records[]) as documented in the DMT JSON Device Integration spec that
 * EzyTrack's Device Manager pushes to us, and upserts the sending device's
 * latest known state.
 *
 * Field types read (spec 10.5): FType 0 (GPS Data) for position, FType 2
 * (Digital Data) for ignition/in-trip, and FType 27 (Device Odometer and Run
 * Hours) for odometer/engine hours. Every other field type is ignored, in
 * keeping with the spec's own "unknown fields should be ignored" contract
 * (section 10.2). Nothing here assumes a company — device-to-fleet-unit
 * resolution happens via integration_mappings, since the payload itself
 * carries no registration number or company reference. Odometer/run hours
 * are then pushed onto the mapped units by EzyTrackSyncService.
 */
class EzyTrackPositionIngestor
{
    /** entity_type values integration_mappings uses for device links — see ResolvesEzyTrackIntegration. */
    public const MAPPING_ENTITY_TYPES = ['horse_ezytrack_device', 'trailer_ezytrack_device', 'vehicle_ezytrack_device'];

    protected const FTYPE_GPS = 0;
    protected const FTYPE_DIGITAL = 2;
    protected const FTYPE_ODOMETER = 27;

    public function ingest(array $packet)
    {
        $serial = (string) $packet['SerNo'];

        $device = EzyTrackDevice::firstOrNew(['serial_number' => $serial]);

        $device->imei = Arr::get($packet, 'IMEI', $device->imei);
        $device->iccid = Arr::get($packet, 'ICCID', $device->iccid);
        $device->prod_id = Arr::get($packet, 'ProdId', $device->prod_id);
        $device->fw = Arr::get($packet, 'FW', $device->fw);
        $device->last_seen_at = now();
        $device->raw_last_payload = json_encode($packet);

        $best = $this->latestRecords(Arr::get($packet, 'Records', []));

        if ($best['record'] !== null) {
            $rec = $best['record'];
            $device->last_seq_no = Arr::get($rec, 'SeqNo', $device->last_seq_no);
            $device->last_reason = Arr::get($rec, 'Reason', $device->last_reason);
            $device->last_record_at = $this->parseDate(Arr::get($rec, 'DateUTC')) ?: $device->last_record_at;
        }

        if ($best['gps'] !== null) {
            $gps = $best['gps'];
            $device->last_gps_at = $this->parseDate(Arr::get($gps, 'GpsUTC')) ?: $device->last_gps_at;
            $device->latitude = Arr::get($gps, 'Lat', $device->latitude);
            $device->longitude = Arr::get($gps, 'Long', $device->longitude);
            $device->altitude = Arr::get($gps, 'Alt', $device->altitude);

            if (Arr::has($gps, 'Spd')) {
                // cm/s -> km/h (see DM spec 10.5.1.3)
                $device->speed_kmh = round($gps['Spd'] * 0.036, 2);
            }

            if (Arr::has($gps, 'Head')) {
                // Deg/2 -> Deg
                $device->heading_deg = $gps['Head'] * 2;
            }

            $device->pos_acc = Arr::get($gps, 'PosAcc', $device->pos_acc);
            $device->gps_status = Arr::get($gps, 'GpsStat', $device->gps_status);
        }

        // A device re-POSTs uncommitted records after an outage (spec 6.1.3), so an
        // older batch can arrive after a newer one — never let it roll these back.
        $odometer = $best['odometer'];
        if ($odometer !== null && $this->isNewer($odometer['date'], $device->odometer_at)) {
            $field = $odometer['field'];

            if (is_numeric(Arr::get($field, 'Odo'))) {
                // 10 m units -> km (spec 10.5.15)
                $device->odometer_km = round($field['Odo'] / 100, 2);
            }
            if (is_numeric(Arr::get($field, 'RH'))) {
                // seconds -> hours
                $device->run_hours = round($field['RH'] / 3600, 2);
            }
            if (is_numeric(Arr::get($field, 'RH2'))) {
                $device->run_hours_2 = round($field['RH2'] / 3600, 2);
            }

            $device->odometer_at = $odometer['date'] ?: $device->odometer_at;
        }

        $digital = $best['digital'];
        if ($digital !== null && $this->isNewer($digital['date'], $device->digital_at)) {
            $field = $digital['field'];

            if (is_numeric(Arr::get($field, 'DIn'))) {
                // DIn bit 0 = ignition (spec 10.5.3)
                $device->ignition_on = ((int) $field['DIn'] & 1) === 1;
            }
            if (is_numeric(Arr::get($field, 'DevStat'))) {
                // DevStat bit 0 = trip status, 1 = "in trip"
                $device->in_trip = ((int) $field['DevStat'] & 1) === 1;
            }

            $device->digital_at = $digital['date'] ?: $device->digital_at;
        }

        $device->save();

        $this->logIfMapped($serial, $device);

        if ($device->odometer_km !== null || $device->run_hours !== null) {
            // Best-effort: a failure here must not make Device Manager re-POST a packet we already stored.
            try {
                app(EzyTrackSyncService::class)->applyReadingsToMappedAssets($device);
            } catch (Throwable $e) {
                Log::warning('EzyTrack mileage apply failed for device ' . $serial . ': ' . $e->getMessage());
            }
        }

        return $device;
    }

    /**
     * Finds, across every Record in this packet:
     *  - the record with the latest DateUTC (for SeqNo/Reason bookkeeping,
     *    even on records with no position data e.g. heartbeats),
     *  - the FType 0 / 2 / 27 field from whichever record most recently
     *    carried one (a batch can mix records with different field sets).
     *
     * @return array{record: array|null, gps: array|null, digital: array{field: array, date: ?Carbon}|null, odometer: array{field: array, date: ?Carbon}|null}
     */
    protected function latestRecords(array $records)
    {
        $latestRecord = null;
        $latestRecordDate = null;
        $latest = [self::FTYPE_GPS => null, self::FTYPE_DIGITAL => null, self::FTYPE_ODOMETER => null];

        foreach ($records as $rec) {
            if (! is_array($rec)) {
                continue;
            }

            $date = $this->parseDate(Arr::get($rec, 'DateUTC'));

            if ($latestRecord === null || ($date && (! $latestRecordDate || $date->gte($latestRecordDate)))) {
                $latestRecord = $rec;
                $latestRecordDate = $date ?: $latestRecordDate;
            }

            foreach (Arr::get($rec, 'Fields', []) as $field) {
                if (! is_array($field)) {
                    continue;
                }

                $type = Arr::get($field, 'FType');
                if (! is_int($type) || ! array_key_exists($type, $latest)) {
                    continue;
                }

                $current = $latest[$type];
                if ($current === null || ($date && (! $current['date'] || $date->gte($current['date'])))) {
                    $latest[$type] = ['field' => $field, 'date' => $date ?: ($current['date'] ?? null)];
                }
            }
        }

        return [
            'record'   => $latestRecord,
            'gps'      => $latest[self::FTYPE_GPS]['field'] ?? null,
            'digital'  => $latest[self::FTYPE_DIGITAL],
            'odometer' => $latest[self::FTYPE_ODOMETER],
        ];
    }

    protected function isNewer(?Carbon $incoming, $stored)
    {
        return ! $stored || ! $incoming || $incoming->gte($stored);
    }

    protected function parseDate($value)
    {
        if (empty($value) || ! is_string($value)) {
            return null;
        }

        try {
            return Carbon::parse($value);
        } catch (Throwable $exception) {
            return null;
        }
    }

    /** Best-effort audit trail — only possible once the device is linked to a company via a mapping. */
    protected function logIfMapped(string $serial, EzyTrackDevice $device)
    {
        $mapping = IntegrationMapping::whereIn('entity_type', self::MAPPING_ENTITY_TYPES)
            ->where('external_reference', $serial)
            ->first();

        if (! $mapping) {
            return;
        }

        IntegrationLog::create([
            'company_integration_id' => $mapping->company_integration_id,
            'direction' => 'inbound',
            'action' => 'webhook',
            'status' => 'ok',
            'message' => 'Position update for EzyTrack device ' . $serial,
            'meta' => [
                'serial' => $serial,
                'latitude' => $device->latitude,
                'longitude' => $device->longitude,
                'gps_at' => $device->last_gps_at ? $device->last_gps_at->toIso8601String() : null,
                'odometer_km' => $device->odometer_km,
                'run_hours' => $device->run_hours,
                'ignition_on' => $device->ignition_on,
            ],
        ]);
    }
}
