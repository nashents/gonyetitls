<?php

namespace App\Services\Freight;

use Carbon\Carbon;
use Illuminate\Support\Collection;

class FreeDayCalculatorService
{
    /**
     * The triggering event day is "day 0"; the free period covers $freeDays
     * full calendar days after it, so the returned date is itself still free.
     */
    public function lastFreeDay(Carbon $startDate, int $freeDays): Carbon
    {
        return $startDate->copy()->startOfDay()->addDays($freeDays);
    }

    /**
     * Chargeable days begin the calendar day after $lastFreeDay, counted
     * through $stopDate (or today if still open). 0 if not yet exceeded.
     */
    public function chargeableDays(Carbon $lastFreeDay, ?Carbon $stopDate): int
    {
        $lastFreeDay = $lastFreeDay->copy()->startOfDay();
        $effectiveEnd = ($stopDate ?? Carbon::now())->copy()->startOfDay();

        if ($effectiveEnd->lte($lastFreeDay)) {
            return 0;
        }

        return $lastFreeDay->diffInDays($effectiveEnd);
    }

    /**
     * Buckets chargeable days across $rateTiers in day_from order (escalating
     * tiers), not a single flat rate applied to every day.
     */
    public function estimateExposure(int $chargeableDays, Collection $rateTiers): float
    {
        $remaining = $chargeableDays;
        $total = 0.0;
        $lastRate = null;

        foreach ($rateTiers->sortBy('day_from') as $tier) {
            if ($remaining <= 0) {
                break;
            }

            $span = is_null($tier->day_to) ? $remaining : ($tier->day_to - $tier->day_from + 1);
            $daysInTier = min($remaining, max(0, $span));
            $total += $daysInTier * (float) $tier->rate;
            $remaining -= $daysInTier;
            $lastRate = (float) $tier->rate;
        }

        // Defensive fallback for an incomplete tier config (no open-ended
        // final tier) — price the remainder at the last known rate rather
        // than silently undercount a real financial estimate.
        if ($remaining > 0 && $lastRate !== null) {
            $total += $remaining * $lastRate;
        }

        return round($total, 2);
    }

    public function status(Carbon $lastFreeDay, ?Carbon $stopDate, int $warnDays = 3): string
    {
        if ($stopDate !== null) {
            return 'stopped';
        }

        $today = Carbon::today();
        $lastFreeDay = $lastFreeDay->copy()->startOfDay();

        if ($today->gt($lastFreeDay)) {
            return 'accruing';
        }

        if ($today->eq($lastFreeDay)) {
            return 'expiring_today';
        }

        if ($today->gte($lastFreeDay->copy()->subDays($warnDays))) {
            return 'expiring_soon';
        }

        return 'within_free_period';
    }
}
