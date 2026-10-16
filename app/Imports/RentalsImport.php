<?php

namespace App\Imports;

use App\Imports\Concerns\SkipsTemplateExampleRows;
use App\Models\Currency;
use App\Models\Customer;
use App\Models\Driver;
use App\Models\Rental;
use App\Models\Transporter;
use App\Models\Vehicle;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithLimit;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

/**
 * Rentals import. Mirrors Rentals\Index::store(): one rental per row.
 *
 * Columns: customer*, vehicle* (registration or fleet number), transporter
 * (defaults to the vehicle's), driver (full name), currency* (e.g. USD),
 * rate_amount*, deposit_amount, pickup_at*, days, due_back_at, returned_at,
 * pickup_odometer, return_odometer, pickup_fuel_level, return_fuel_level
 * (0-100), status (reserved/active/closed/cancelled, default reserved),
 * transporter_rate_amount (fills the transporter agreement), notes.
 * Give days or due_back_at - the other is worked out.
 *
 * Customers, vehicles and currencies are matched, never created; rows that
 * can't be matched are skipped and reported back via $skipped.
 */
class RentalsImport implements ToCollection, SkipsEmptyRows, WithHeadingRow, WithLimit
{
    use SkipsTemplateExampleRows;
    use Importable;

    const STATUSES = ['reserved', 'active', 'closed', 'cancelled'];

    public int $created = 0;
    public array $skipped = [];

    public function limit(): int
    {
        return 2500;
    }

    public function collection(Collection $rows)
    {
        $company = Auth::user()->employee?->company;

        foreach ($rows as $row) {
            $label = trim($row->get('customer') . ' / ' . $row->get('vehicle'), ' /') ?: 'a row';

            $customer = $this->match(Customer::class, 'name', $row->get('customer'));
            if (!$customer) {
                $this->skipped[] = "{$label}: customer not found";
                continue;
            }

            $vehicleRef = trim((string) $row->get('vehicle'));
            $vehicle = $vehicleRef === '' ? null : Vehicle::where(function ($q) use ($vehicleRef) {
                $ref = strtolower(str_replace(' ', '', $vehicleRef));
                $q->whereRaw("LOWER(REPLACE(registration_number, ' ', '')) = ?", [$ref])
                  ->orWhereRaw("LOWER(REPLACE(fleet_number, ' ', '')) = ?", [$ref]);
            })->first();
            if (!$vehicle) {
                $this->skipped[] = "{$label}: vehicle not found";
                continue;
            }

            $currency = $this->match(Currency::class, 'name', $row->get('currency'));
            if (!$currency) {
                $this->skipped[] = "{$label}: currency not found";
                continue;
            }

            $pickupAt = $this->date($row->get('pickup_at'));
            if (!$pickupAt) {
                $this->skipped[] = "{$label}: pickup_at is missing or not a date";
                continue;
            }

            $rate = $row->get('rate_amount');
            if (!is_numeric($rate) || $rate < 0) {
                $this->skipped[] = "{$label}: rate_amount must be a number";
                continue;
            }

            $dueBackAt = $this->date($row->get('due_back_at'));
            $days = $row->get('days');
            if (is_numeric($days) && $days > 0) {
                $dueBackAt = $dueBackAt ?: $pickupAt->copy()->addDays((int) ceil($days));
            } elseif ($dueBackAt) {
                $days = max(1, $pickupAt->diffInDays($dueBackAt));
            } else {
                $this->skipped[] = "{$label}: give days or due_back_at";
                continue;
            }

            $status = strtolower(trim((string) $row->get('status'))) ?: 'reserved';
            if (!in_array($status, self::STATUSES, true)) {
                $this->skipped[] = "{$label}: status must be one of " . implode(', ', self::STATUSES);
                continue;
            }

            $transporter = $this->match(Transporter::class, 'name', $row->get('transporter'));
            $transporterRate = $row->get('transporter_rate_amount');

            $rental = new Rental();
            $number = $this->rentalNumber($company?->name);
            $rental->rental_number = $number;
            $rental->car_rental_number = $number;
            $rental->company_id = $company?->id;
            $rental->user_id = Auth::id();
            $rental->customer_id = $customer->id;
            $rental->transporter_id = $transporter?->id ?? $vehicle->transporter_id;
            $rental->driver_id = $this->driverId($row->get('driver'), $customer->id);
            $rental->vehicle_id = $vehicle->id;
            $rental->currency_id = $currency->id;
            $rental->rate_amount = $rate;
            $rental->deposit_amount = is_numeric($row->get('deposit_amount')) ? $row->get('deposit_amount') : 0;
            $rental->transporter_agreement = is_numeric($transporterRate);
            if (is_numeric($transporterRate)) {
                $rental->transporter_rate_amount = $transporterRate;
            }
            $rental->notes = $row->get('notes');
            $rental->pickup_at = $pickupAt;
            $rental->days = $days;
            $rental->due_back_at = $dueBackAt;
            $rental->returned_at = $this->date($row->get('returned_at'));
            $rental->pickup_odometer = $this->number($row->get('pickup_odometer'));
            $rental->return_odometer = $this->number($row->get('return_odometer'));
            $rental->pickup_fuel_level = $this->fuelLevel($row->get('pickup_fuel_level'));
            $rental->return_fuel_level = $this->fuelLevel($row->get('return_fuel_level'));
            $rental->status = $status;
            $rental->save();

            $this->created++;
        }
    }

    private function match(string $model, string $column, $value)
    {
        $value = strtolower(trim((string) $value));

        return $value === '' ? null : $model::whereRaw("LOWER(TRIM({$column})) = ?", [$value])->first();
    }

    private function driverId($name, $customerId): ?int
    {
        $name = strtolower(trim((string) $name));
        if ($name === '') {
            return null;
        }
        $byName = fn ($q) => $q->whereRaw("LOWER(TRIM(CONCAT(name, ' ', surname))) = ?", [$name]);

        return Driver::where('customer_id', $customerId)->where($byName)->value('id')
            ?? Driver::where($byName)->value('id')
            ?? Driver::whereHas('employee', $byName)->value('id');
    }

    private function date($value): ?Carbon
    {
        if (blank($value)) {
            return null;
        }
        try {
            return is_numeric($value)
                ? Carbon::instance(ExcelDate::excelToDateTimeObject($value))
                : Carbon::parse($value);
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function number($value)
    {
        return is_numeric($value) ? $value : null;
    }

    private function fuelLevel($value)
    {
        return is_numeric($value) ? max(0, min(100, (int) $value)) : null;
    }

    /** Same format as Rentals\Index::rentalNumber(). */
    private function rentalNumber(?string $companyName): string
    {
        $words = explode(' ', trim((string) $companyName));
        $initials = ($words[0][0] ?? '') . ($words[1][0] ?? '');
        $next = (Rental::withTrashed()->max('id') ?? 0) + 1;

        return $initials . 'R' . str_pad($next, 5, '0', STR_PAD_LEFT);
    }
}
