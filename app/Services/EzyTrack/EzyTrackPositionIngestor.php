<?php

namespace App\Services\EzyTrack;

use App\Models\EzyTrackDevice;
use App\Models\IntegrationLog;
use App\Models\IntegrationMapping;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Throwable;

/**
 * Parses one Digital Matter "base packet" (SerNo/IMEI/ICCID/ProdId/FW +
 * Records[]) as documented in the DMT JSON Device Integration spec that
 * EzyTrack's Device Manager pushes to us, and upserts the sending device's
 * latest known state.
 *
 * Only FType 0 (GPS Data) is read for position — every other field type
 * (digital I/O, analogues, cell/wifi scans, etc.) is ignored for now, in
 * keeping with the spec's own "unknown fields should be ignored" contract
 * (section 10.2). Nothing here assumes a company — device-to-fleet-unit
 * resolution happens later via integration_mappings, since the payload
 * itself carries no registration number or company reference.
 */
class EzyTrackPositionIngestor
{
    /** entity_type values integration_mappings uses for device links — see ResolvesEzyTrackIntegration. */
    public const MAPPING_ENTITY_TYPES = ['horse_ezytrack_device', 'trailer_ezytrack_device', 'vehicle_ezytrack_device'];

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

        $device->save();

        $this->logIfMapped($serial, $device);

        return $device;
    }

    /**
     * Finds, across every Record in this packet:
     *  - the record with the latest DateUTC (for SeqNo/Reason bookkeeping,
     *    even on records with no position data e.g. heartbeats),
     *  - the FType 0 field from whichever record most recently carried one
     *    (a batch can mix positioned and non-positioned records).
     *
     * @return array{record: array|null, gps: array|null}
     */
    protected function latestRecords(array $records)
    {
        $latestRecord = null;
        $latestRecordDate = null;
        $latestGps = null;
        $latestGpsDate = null;

        foreach ($records as $rec) {
            if (! is_array($rec)) {
                continue;
            }

            $date = $this->parseDate(Arr::get($rec, 'DateUTC'));

            if ($latestRecord === null || ($date && (! $latestRecordDate || $date->gte($latestRecordDate)))) {
                $latestRecord = $rec;
                $latestRecordDate = $date ?: $latestRecordDate;
            }

            $gps = null;
            foreach (Arr::get($rec, 'Fields', []) as $field) {
                if (is_array($field) && Arr::get($field, 'FType') === 0) {
                    $gps = $field;
                    break;
                }
            }

            if ($gps !== null && ($latestGps === null || ($date && (! $latestGpsDate || $date->gte($latestGpsDate))))) {
                $latestGps = $gps;
                $latestGpsDate = $date ?: $latestGpsDate;
            }
        }

        return ['record' => $latestRecord, 'gps' => $latestGps];
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
            ],
        ]);
    }
}
