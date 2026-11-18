<?php

namespace App\Exports\Concerns;

use App\Models\Shift;
use App\Models\Trip;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Monthly shift-trip reports (Ops Flash, Concentrates Haulage): production day of
 * a shift and how a shift's fuel / mileage is shared across its trips.
 */
trait SharesShiftTrips
{
    /** Production day = shift start − 2h (02:00 to 01:00 next day, as the Production Report). */
    protected function productionDay(Shift $shift): ?Carbon
    {
        $value = trim((string) $shift->shift_start_time);

        try {
            if (preg_match('/^\d{4}-\d{2}-\d{2}[T ]\d{1,2}:\d{2}/', $value)) {
                $start = Carbon::parse(str_replace('T', ' ', substr($value, 0, 16)));
            } elseif (preg_match('/^(\d{1,2}):(\d{2})/', $value, $m) && filled($shift->date)) {
                $start = Carbon::parse($shift->date)->startOfDay()->setTime((int) $m[1], (int) $m[2]);
            } else {
                return null;
            }
        } catch (\Throwable $e) {
            return null;
        }

        return $start->subHours(2)->startOfDay();
    }

    /**
     * Each trip's share of its shift: by trip mileage when every trip has one,
     * else equally per load. 'km' holds the trip mileages found.
     */
    protected function shares(Collection $shiftTrips): array
    {
        $km = [];
        foreach ($shiftTrips as $trip) {
            if (($run = $this->tripKm($trip)) !== null) {
                $km[$trip->id] = $run;
            }
        }

        $count = $shiftTrips->count();
        $totalKm = array_sum($km);
        $byKm = count($km) === $count && $totalKm > 0;

        $by = [];
        foreach ($shiftTrips as $trip) {
            $by[$trip->id] = $byKm ? $km[$trip->id] / $totalKm : 1 / $count;
        }

        return ['by' => $by, 'km' => $km, 'loads' => 1 / $count];
    }

    protected function tripKm(Trip $trip): ?float
    {
        if (is_numeric($trip->starting_mileage) && is_numeric($trip->ending_mileage)) {
            $run = (float) $trip->ending_mileage - (float) $trip->starting_mileage;
            if ($run > 0) {
                return $run;
            }
        }

        return is_numeric($trip->distance) && $trip->distance > 0 ? (float) $trip->distance : null;
    }

    protected function shiftKm(?Shift $shift): float
    {
        if (!$shift) {
            return 0.0;
        }

        if (is_numeric($shift->open_mileage) && is_numeric($shift->close_mileage)) {
            $run = (float) $shift->close_mileage - (float) $shift->open_mileage;
            if ($run > 0) {
                return $run;
            }
        }

        return is_numeric($shift->actual_mileage) && $shift->actual_mileage > 0 ? (float) $shift->actual_mileage : 0.0;
    }
}
