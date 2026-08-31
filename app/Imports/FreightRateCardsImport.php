<?php

namespace App\Imports;

use App\Imports\Concerns\ResolvesLookups;
use App\Models\Cargo;
use App\Models\ChargeType;
use App\Models\Currency;
use App\Models\Customer;
use App\Models\FreightRateCard;
use App\Models\Location;
use App\Models\Vendor;
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

class FreightRateCardsImport implements ToCollection, SkipsEmptyRows, WithLimit,
    WithHeadingRow, SkipsOnError, WithValidation, WithChunkReading, WithBatchInserts
{
    use Importable, SkipsErrors, ResolvesLookups;

    protected array $skipped = [];
    protected int $rowNumber = 1;
    public int $rowsCreated = 0;

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

            $direction = strtolower((string) $row->get('direction'));
            if (!array_key_exists($direction, FreightRateCard::DIRECTIONS)) {
                $this->skipped[] = ['row' => $this->rowNumber, 'reason' => 'invalid_direction', 'value' => $row->get('direction')];
                continue;
            }

            $currency = $this->resolveOnly(Currency::class, $row->get('currency'));
            if (!$currency) {
                $this->skipped[] = ['row' => $this->rowNumber, 'reason' => 'currency_not_found', 'currency' => $row->get('currency')];
                continue;
            }

            $vendor = null;
            $customer = null;

            if ($direction === 'buy') {
                $vendor = $this->resolveOnly(Vendor::class, $row->get('vendor'));
                if (!$vendor) {
                    $this->skipped[] = ['row' => $this->rowNumber, 'reason' => 'vendor_not_found', 'vendor' => $row->get('vendor')];
                    continue;
                }
            } else {
                $customer = $this->resolveOnly(Customer::class, $row->get('customer'));
                if (!$customer) {
                    $this->skipped[] = ['row' => $this->rowNumber, 'reason' => 'customer_not_found', 'customer' => $row->get('customer')];
                    continue;
                }
            }

            $rateBasis = $row->get('rate_basis') ? strtolower(trim($row->get('rate_basis'))) : null;
            if ($rateBasis && !array_key_exists($rateBasis, FreightRateCard::RATE_BASES)) {
                $this->skipped[] = ['row' => $this->rowNumber, 'reason' => 'invalid_rate_basis', 'value' => $row->get('rate_basis')];
                continue;
            }

            $markupType = $row->get('markup_type') ? strtolower(trim($row->get('markup_type'))) : null;
            if ($markupType && !array_key_exists($markupType, FreightRateCard::MARKUP_TYPES)) {
                $this->skipped[] = ['row' => $this->rowNumber, 'reason' => 'invalid_markup_type', 'value' => $row->get('markup_type')];
                continue;
            }

            $chargeType = $this->resolveOrCreate(ChargeType::class, $row->get('charge_type'));
            $originLocation = $this->resolveOnly(Location::class, $row->get('origin_location'));
            $destinationLocation = $this->resolveOnly(Location::class, $row->get('destination_location'));
            $cargo = $this->resolveOnly(Cargo::class, $row->get('cargo'));

            FreightRateCard::create([
                'user_id' => Auth::id(),
                'direction' => $direction,
                'vendor_id' => $vendor?->id,
                'customer_id' => $customer?->id,
                'charge_type_id' => $chargeType?->id,
                'mode' => $row->get('mode') ?: null,
                'container_type' => $row->get('container_type') ?: null,
                'origin_location_id' => $originLocation?->id,
                'destination_location_id' => $destinationLocation?->id,
                'cargo_id' => $cargo?->id,
                'currency_id' => $currency->id,
                'rate_basis' => $rateBasis,
                'rate' => is_numeric($row->get('rate')) ? (float) $row->get('rate') : null,
                'markup_type' => $direction === 'sell' ? $markupType : null,
                'markup_value' => $direction === 'sell' && is_numeric($row->get('markup_value')) ? (float) $row->get('markup_value') : null,
                'effective_from' => $this->parseExcelDate($row->get('effective_from')),
                'effective_to' => $this->parseExcelDate($row->get('effective_to')),
                'is_active' => true,
                'notes' => $row->get('notes') ?: null,
            ]);

            $this->rowsCreated++;
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
