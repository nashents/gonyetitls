<?php

namespace App\Imports;

use App\Imports\Concerns\ResolvesLookups;
use App\Models\Country;
use App\Models\Currency;
use App\Models\Customer;
use App\Models\FreightServiceType;
use App\Models\Location;
use App\Services\Freight\FreightJobService;
use App\Services\Freight\JobProfitabilityCalculator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithLimit;
use Maatwebsite\Excel\Concerns\WithValidation;

class FreightJobsImport implements ToCollection, SkipsEmptyRows, WithLimit,
    WithHeadingRow, SkipsOnError, WithValidation, WithChunkReading, WithBatchInserts
{
    use Importable, SkipsErrors, ResolvesLookups;

    protected array $skipped = [];
    protected int $rowNumber = 1;
    public int $rowsCreated = 0;

    protected FreightJobService $freightJobService;
    protected $company;

    public function __construct()
    {
        $this->freightJobService = app(FreightJobService::class);
        $this->company = Auth::user()->employee?->company ?? Auth::user()->company ?? null;
    }

    public function limit(): int
    {
        return 2500;
    }

    public function batchSize(): int
    {
        return 150;
    }

    public function chunkSize(): int
    {
        return 150;
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            $this->rowNumber++;

            if ($row->filter()->isEmpty()) {
                continue;
            }

            $row = $row->map(fn ($v) => is_string($v) ? trim($v) : $v);

            $customer = $this->resolveOnly(Customer::class, $row->get('customer'));
            if (!$customer) {
                $this->skipped[] = ['row' => $this->rowNumber, 'reason' => 'customer_not_found', 'customer' => $row->get('customer')];
                continue;
            }

            $serviceType = $this->resolveOnly(FreightServiceType::class, $row->get('freight_service_type'));
            if (!$serviceType) {
                $this->skipped[] = ['row' => $this->rowNumber, 'reason' => 'freight_service_type_not_found', 'freight_service_type' => $row->get('freight_service_type')];
                continue;
            }

            $currency = $this->resolveOnly(Currency::class, $row->get('currency'));
            if (!$currency) {
                $this->skipped[] = ['row' => $this->rowNumber, 'reason' => 'currency_not_found', 'currency' => $row->get('currency')];
                continue;
            }

            $originCountry = $this->resolveOnly(Country::class, $row->get('origin_country'));
            if (!$originCountry) {
                $this->skipped[] = ['row' => $this->rowNumber, 'reason' => 'origin_country_not_found', 'origin_country' => $row->get('origin_country')];
                continue;
            }

            $destinationCountry = $this->resolveOnly(Country::class, $row->get('destination_country'));
            if (!$destinationCountry) {
                $this->skipped[] = ['row' => $this->rowNumber, 'reason' => 'destination_country_not_found', 'destination_country' => $row->get('destination_country')];
                continue;
            }

            $mode = $row->get('primary_transport_mode') ? strtolower(trim($row->get('primary_transport_mode'))) : null;
            if ($mode && !in_array($mode, JobProfitabilityCalculator::TRANSPORT_MODES, true)) {
                $this->skipped[] = ['row' => $this->rowNumber, 'reason' => 'invalid_transport_mode', 'value' => $row->get('primary_transport_mode')];
                continue;
            }

            $status = $row->get('status') ? strtolower(trim($row->get('status'))) : 'draft';
            if (!in_array($status, JobProfitabilityCalculator::STATUSES, true)) {
                $this->skipped[] = ['row' => $this->rowNumber, 'reason' => 'invalid_status', 'value' => $row->get('status')];
                continue;
            }

            $portOfLoading = $this->resolveOnly(Location::class, $row->get('port_of_loading'));
            $portOfDischarge = $this->resolveOnly(Location::class, $row->get('port_of_discharge'));

            try {
                $this->freightJobService->create(
                    [
                        'company_id' => $this->company?->id,
                        'customer_id' => $customer->id,
                        'freight_service_type_id' => $serviceType->id,
                        'currency_id' => $currency->id,
                        'origin_country_id' => $originCountry->id,
                        'destination_country_id' => $destinationCountry->id,
                        'primary_transport_mode' => $mode,
                        'status' => $status,
                        'customer_reference' => $row->get('customer_reference') ?: null,
                        'import_export_type' => $row->get('import_export_type') ?: null,
                        'shipment_type' => $row->get('shipment_type') ?: null,
                        'origin' => $row->get('origin') ?: null,
                        'destination' => $row->get('destination') ?: null,
                        'incoterm' => $row->get('incoterm') ?: null,
                        'opened_at' => $this->parseExcelDate($row->get('opened_at')) ?? now(),
                        'notes' => $row->get('notes') ?: null,
                    ],
                    [
                        'mode' => $mode,
                        'shipment_type' => $row->get('shipment_type') ?: null,
                        'port_of_loading_id' => $portOfLoading?->id,
                        'port_of_discharge_id' => $portOfDischarge?->id,
                        'booking_reference' => $row->get('booking_reference') ?: null,
                        'cargo_description' => $row->get('cargo_description') ?: null,
                        'gross_weight' => is_numeric($row->get('gross_weight')) ? (float) $row->get('gross_weight') : null,
                        'volume_cbm' => is_numeric($row->get('volume_cbm')) ? (float) $row->get('volume_cbm') : null,
                        'package_count' => is_numeric($row->get('package_count')) ? (int) $row->get('package_count') : null,
                    ]
                );

                $this->rowsCreated++;
            } catch (\Throwable $e) {
                $this->skipped[] = ['row' => $this->rowNumber, 'reason' => 'creation_failed', 'error' => $e->getMessage()];
            }
        }
    }

    public function getSkippedRows(): array
    {
        return $this->skipped;
    }

    public function rules(): array
    {
        return [];
    }
}
