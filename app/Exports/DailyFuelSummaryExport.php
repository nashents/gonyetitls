<?php

namespace App\Exports;

use App\Exports\Concerns\DailyShiftReportHeader;
use App\Models\Fuel;
use App\Models\Shift;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

/**
 * Monthly "Daily Fuel Summary" (client's DAILY FUEL SUMMARY workbook): litres per
 * unit per day for the month, on three sheets in the template's order:
 *
 *  - LONG HAUL FUEL / SHORT HAUL FUEL: one column per truck (fleet number).
 *  - LIGHT VEHICLES FUEL: one column per vehicle / equipment, with TOTAL FUEL,
 *    TOTAL KM (odometer run) and CONSUMPTION (km per litre) rows.
 *
 * Source: non-rejected fuel orders. A shift's fuel order counts on the shift's
 * production day (02:00 to 01:00 next day, as the daily Production Report);
 * any other fuel order counts on its own date.
 *
 * Haul: the order's Allocation To, else its shift's trips' haulage type, else its
 * trip's haulage type. Vehicle orders with no haul go to Light Vehicles; horse
 * orders with no haul go to a 4th sheet, UNALLOCATED TRUCK FUEL (same layout as
 * the haul sheets), so no litres drop off and none are guessed into a haul.
 */
class DailyFuelSummaryExport implements WithMultipleSheets
{
    use DailyShiftReportHeader;

    public const LONG = 'long_haul';
    public const SHORT = 'short_haul';
    public const LIGHT = 'light';
    public const UNALLOCATED = 'unallocated';

    protected Carbon $month;

    public function __construct(?Carbon $month = null)
    {
        $this->month = ($month ?: now())->copy()->startOfMonth();
    }

    public function sheets(): array
    {
        $data = $this->collect();

        return [
            new DailyFuelSummaryHaulSheet($this->month, 'LONG HAUL FUEL', $data[self::LONG]['units'], $data[self::LONG]['litres']),
            new DailyFuelSummaryHaulSheet($this->month, 'SHORT HAUL FUEL', $data[self::SHORT]['units'], $data[self::SHORT]['litres']),
            new DailyFuelSummaryLightVehiclesSheet($this->month, $data[self::LIGHT]['units'], $data[self::LIGHT]['litres'], $data[self::LIGHT]['km']),
            new DailyFuelSummaryHaulSheet($this->month, 'UNALLOCATED TRUCK FUEL', $data[self::UNALLOCATED]['units'], $data[self::UNALLOCATED]['litres']),
        ];
    }

    /**
     * @return array<string, array{units: array<string,string>, litres: array<string, array<int,float>>, km?: array<string,float>}>
     */
    protected function collect(): array
    {
        $start = $this->month->copy();
        $end = $this->month->copy()->endOfMonth();

        // Shifts whose production day falls in the month: start 02:00 on day 1 to 01:59 on the 1st of next month
        $shiftIds = Shift::query()
            ->select('id')
            ->where(fn (Builder $q) => $this->whereShiftStartBetween(
                $q,
                $start->copy()->addHours(2),
                $end->copy()->addDay()->startOfDay()->addHour()->addMinutes(59)
            ));

        // Wide net (fuels.date may carry a time, "Y-m-d\TH:i"); productionDay() below decides the day.
        $fuels = Fuel::query()
            ->where(fn (Builder $q) => $q->whereNull('authorization')->orWhere('authorization', '!=', 'rejected'))
            ->where(fn (Builder $q) => $q
                ->whereIn('shift_id', $shiftIds)
                ->orWhere(fn (Builder $f) => $f
                    ->where('date', '>=', $start->copy()->subDay()->toDateString())
                    ->where('date', '<', $end->copy()->addDays(2)->toDateString())))
            ->with(['horse', 'vehicle', 'shift.trips:id,shift_id,haulage_type', 'trip:id,haulage_type'])
            ->get();

        $data = [];
        foreach ([self::LONG, self::SHORT, self::LIGHT, self::UNALLOCATED] as $k) {
            $data[$k] = ['units' => [], 'litres' => [], 'odometers' => []];
        }

        foreach ($fuels as $fuel) {
            $day = $this->productionDay($fuel);
            if (!$day || $day->lt($start) || $day->gt($end)) {
                continue;
            }

            [$key, $unit, $isVehicle] = $this->unit($fuel);
            if (!$key) {
                continue;
            }

            $haul = $this->haul($fuel);
            $group = $haul ?: ($isVehicle ? self::LIGHT : self::UNALLOCATED);
            $label = $group === self::LIGHT ? $this->lightLabel($unit) : $this->fleetLabel($unit);

            $data[$group]['units'][$key] = $label;
            $data[$group]['litres'][$key][$day->day] = ($data[$group]['litres'][$key][$day->day] ?? 0) + (float) $fuel->quantity;

            if ($group === self::LIGHT && is_numeric($fuel->odometer) && $fuel->odometer > 0) {
                $data[$group]['odometers'][$key][] = (float) $fuel->odometer;
            }
        }

        foreach ($data as $group => &$g) {
            uasort($g['units'], 'strnatcasecmp');
        }
        unset($g);

        $data[self::LIGHT]['km'] = $this->lightKilometres($data[self::LIGHT]['units'], $data[self::LIGHT]['odometers'], $start);

        return $data;
    }

    /** Shift fuel: the shift's production day (start − 2h); other fuel: its own date. */
    protected function productionDay(Fuel $fuel): ?Carbon
    {
        if ($fuel->shift_id && $fuel->shift) {
            $start = $this->shiftStart($fuel->shift);
            if ($start) {
                return $start->subHours(2)->startOfDay();
            }
        }

        try {
            return filled($fuel->date) ? Carbon::parse($fuel->date)->startOfDay() : null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    /** shift_start_time is "Y-m-d\TH:i" (form) or "H:i[:s]" with the day in shifts.date (imports). */
    protected function shiftStart(Shift $shift): ?Carbon
    {
        $value = trim((string) $shift->shift_start_time);

        try {
            if (preg_match('/^\d{4}-\d{2}-\d{2}[T ]\d{1,2}:\d{2}/', $value)) {
                return Carbon::parse(str_replace('T', ' ', substr($value, 0, 16)));
            }
            if (preg_match('/^(\d{1,2}):(\d{2})/', $value, $m) && filled($shift->date)) {
                return Carbon::parse($shift->date)->startOfDay()->setTime((int) $m[1], (int) $m[2]);
            }
        } catch (\Throwable $e) {
            return null;
        }

        return null;
    }

    protected function haul(Fuel $fuel): ?string
    {
        $valid = [self::LONG, self::SHORT];

        if (in_array($fuel->allocation_to, $valid, true)) {
            return $fuel->allocation_to;
        }

        $shiftHaul = $fuel->shift?->trips->pluck('haulage_type')->first(fn ($h) => in_array($h, $valid, true));
        if ($shiftHaul) {
            return $shiftHaul;
        }

        return in_array($fuel->trip?->haulage_type, $valid, true) ? $fuel->trip->haulage_type : null;
    }

    /** [key, unit model, is vehicle]; a vehicle_id wins over horse_id (type "Vehicle" orders). */
    protected function unit(Fuel $fuel): array
    {
        if ($fuel->vehicle_id && $fuel->vehicle) {
            return ['v' . $fuel->vehicle_id, $fuel->vehicle, true];
        }
        if ($fuel->horse_id && $fuel->horse) {
            return ['h' . $fuel->horse_id, $fuel->horse, false];
        }

        return [null, null, false];
    }

    protected function fleetLabel($unit): string
    {
        $fleet = trim((string) $unit->fleet_number);

        if ($fleet !== '' && !preg_match('#^n[/?]?a$#i', $fleet)) {
            return $fleet;
        }

        return (string) ($unit->registration_number ?: ('#' . $unit->id));
    }

    /** "REG (FLEET)" like the template's "AGV2233 MR KULEYA" headers. */
    protected function lightLabel($unit): string
    {
        $reg = trim((string) $unit->registration_number);
        $fleet = trim((string) $unit->fleet_number);

        if (preg_match('#^n[/?]?a$#i', $fleet) || strcasecmp($fleet, $reg) === 0) {
            $fleet = '';
        }

        if ($reg === '') {
            return $fleet !== '' ? $fleet : ('#' . $unit->id);
        }

        return $fleet !== '' ? "{$reg} ({$fleet})" : $reg;
    }

    /**
     * Odometer run for the month: highest reading in the month minus the last
     * reading before the month (else the month's lowest reading).
     *
     * @return array<string, float>
     */
    protected function lightKilometres(array $units, array $odometers, Carbon $start): array
    {
        $km = [];

        foreach (array_keys($units) as $key) {
            $readings = $odometers[$key] ?? [];
            if (!$readings) {
                $km[$key] = 0.0;
                continue;
            }

            $id = (int) substr($key, 1);
            $previous = Fuel::query()
                ->where($key[0] === 'v' ? 'vehicle_id' : 'horse_id', $id)
                ->where('date', '<', $start->toDateString())
                ->where('odometer', '>', 0)
                ->where(fn (Builder $q) => $q->whereNull('authorization')->orWhere('authorization', '!=', 'rejected'))
                ->orderByDesc('date')
                ->orderByDesc('id')
                ->value('odometer');

            $from = is_numeric($previous) && (float) $previous <= max($readings) ? (float) $previous : min($readings);
            $km[$key] = max(0.0, max($readings) - $from);
        }

        return $km;
    }
}
