<?php

namespace App\Imports;

use App\Models\EzyTrackDevice;
use App\Models\Horse;
use App\Models\IntegrationMapping;
use App\Models\Trailer;
use App\Models\Vehicle;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithLimit;

/**
 * Bulk-links EzyTrack devices to Horses/Trailers/Vehicles from EzyTrack's
 * "Asset Listing" export (columns: Asset Id, Asset Type, Name, Description,
 * Serial Number). Row 1 of that export is EzyTrack's logo, not data — the
 * real header sits on row 2 (see headingRow()).
 *
 * "Name" holds the registration number plus a SIM-type suffix in parentheses,
 * e.g. "AEZ 4095 (Int Sim)" — the suffix is stripped before matching against
 * {Horse,Trailer,Vehicle}.registration_number.
 *
 * "Serial Number" is usually the Digital Matter SerNo, but some exported rows
 * carry the device's IMEI there instead (seen in a real export sample) — the
 * matcher checks both ezytrack_devices.serial_number and .imei before
 * deciding this is a brand new, never-seen device.
 */
class EzyTrackDeviceMappingsImport implements ToCollection, SkipsEmptyRows, WithLimit, WithHeadingRow, SkipsOnError
{
    use Importable, SkipsErrors;

    /** EzyTrack "Asset Type" -> our fleet-unit entity key. Extend as new asset types are seen. */
    protected const ASSET_TYPE_MAP = [
        'trucks'   => 'horse',
        'truck'    => 'horse',
        'horses'   => 'horse',
        'horse'    => 'horse',
        'trailers' => 'trailer',
        'trailer'  => 'trailer',
        'vehicles' => 'vehicle',
        'vehicle'  => 'vehicle',
    ];

    protected const ENTITY_MODELS = [
        'horse'   => Horse::class,
        'trailer' => Trailer::class,
        'vehicle' => Vehicle::class,
    ];

    protected int $companyIntegrationId;
    protected $companyId;
    protected array $skipped = [];
    protected int $matched = 0;
    protected int $rowNumber = 2; // heading is row 2, so first data row is row 3

    public function __construct(int $companyIntegrationId, $companyId = null)
    {
        $this->companyIntegrationId = $companyIntegrationId;
        $this->companyId = $companyId;
    }

    public function headingRow(): int
    {
        return 2;
    }

    public function limit(): int
    {
        return 5000;
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            $this->rowNumber++;

            if ($row->filter()->isEmpty()) {
                continue;
            }

            // One-off check on the first data row: confirm the headings we rely
            // on actually exist, so a renamed column shows up as a clear log
            // entry instead of every row silently skipping.
            if ($this->rowNumber === 3) {
                $expected = ['asset_type', 'name', 'serial_number'];
                $missing = array_filter($expected, fn ($h) => ! $row->has($h));
                if (! empty($missing)) {
                    Log::warning('EzyTrackDeviceMappingsImport: expected column heading(s) not found in file', [
                        'missing_headings' => array_values($missing),
                        'headings_found'   => $row->keys()->all(),
                    ]);
                }
            }

            $serial = trim((string) ($row['serial_number'] ?? ''));
            $assetType = strtolower(trim((string) ($row['asset_type'] ?? '')));
            $name = (string) ($row['name'] ?? '');

            if ($serial === '') {
                $this->skipped[] = ['row' => $this->rowNumber, 'reason' => 'missing_serial_number', 'name' => $name];
                continue;
            }

            $entityType = self::ASSET_TYPE_MAP[$assetType] ?? null;
            if (! $entityType) {
                $this->skipped[] = ['row' => $this->rowNumber, 'reason' => 'unknown_asset_type', 'asset_type' => $assetType, 'name' => $name];
                continue;
            }

            // Strip the trailing "(Int Sim)" / "(INT SIM)" / malformed ")Int sim)"
            // suffix — everything from the first parenthesis character onward.
            $registration = trim(preg_replace('/[()].*$/', '', $name));
            if ($registration === '') {
                $this->skipped[] = ['row' => $this->rowNumber, 'reason' => 'missing_registration_number'];
                continue;
            }

            $modelClass = self::ENTITY_MODELS[$entityType];

            $model = $modelClass::query()
                ->when($this->companyId, function ($q) {
                    $q->whereHas('transporter', fn ($tq) => $tq->where('company_id', $this->companyId));
                })
                ->whereRaw('LOWER(registration_number) = ?', [strtolower($registration)])
                ->first();

            if (! $model) {
                $this->skipped[] = [
                    'row'                  => $this->rowNumber,
                    'reason'               => 'no_matching_' . $entityType,
                    'registration_number'  => $registration,
                ];
                continue;
            }

            $device = EzyTrackDevice::where('serial_number', $serial)
                ->orWhere('imei', $serial)
                ->first();

            if (! $device) {
                $device = EzyTrackDevice::create(['serial_number' => $serial]);
            }

            IntegrationMapping::updateOrCreate(
                [
                    'company_integration_id' => $this->companyIntegrationId,
                    'entity_type'            => $entityType . '_ezytrack_device',
                    'local_id'               => $model->id,
                ],
                [
                    'local_model'        => $modelClass,
                    'local_reference'    => $model->fleet_number ?? $model->registration_number,
                    'external_id'        => $device->serial_number,
                    'external_reference' => $device->serial_number,
                    'sync_status'        => IntegrationMapping::STATUS_SYNCED,
                    'last_synced_at'     => now(),
                ]
            );

            $this->matched++;
        }
    }

    public function getSkippedRows(): array
    {
        return $this->skipped;
    }

    public function getMatchedCount(): int
    {
        return $this->matched;
    }
}
