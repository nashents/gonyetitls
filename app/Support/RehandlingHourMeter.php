<?php

namespace App\Support;

/**
 * Hour-meter check for a rehandling work row: the machine cannot run longer
 * than the work's Start Time -> Close Time, and Close Hours cannot be below
 * Open Hours. Keeps Machine Hrs <= Man Hrs on the Rehandling Production report.
 */
class RehandlingHourMeter
{
    /** Meter readings are usually to one decimal, so allow that much rounding. */
    public const TOLERANCE_HOURS = 0.1;

    /** Error message for the Close Hours field, or null when the row is fine or incomplete. */
    public static function problem($startTime, $stopTime, $openHours, $closeHours): ?string
    {
        if (!is_numeric($openHours) || !is_numeric($closeHours)) {
            return null;
        }

        $run = (float) $closeHours - (float) $openHours;

        if ($run < 0) {
            return 'Close hours cannot be less than open hours.';
        }

        $workHours = self::workHours($startTime, $stopTime);

        if ($workHours === null || $run <= $workHours + self::TOLERANCE_HOURS) {
            return null;
        }

        return sprintf(
            'Hour meter run (%s hrs) is more than the work time %s to %s (%s hrs). Check the open/close hours or times.',
            self::fmt($run),
            $startTime,
            $stopTime,
            self::fmt($workHours)
        );
    }

    /** Start -> Close time ("H:i") in hours, wrapping past midnight; null if either is missing. */
    public static function workHours($startTime, $stopTime): ?float
    {
        $start = self::minutesOfDay($startTime);
        $stop = self::minutesOfDay($stopTime);

        if ($start === null || $stop === null) {
            return null;
        }

        if ($stop < $start) {
            $stop += 24 * 60;
        }

        return ($stop - $start) / 60;
    }

    protected static function minutesOfDay($time): ?int
    {
        if (blank($time) || !preg_match('/(\d{1,2}):(\d{2})/', (string) $time, $m)) {
            return null;
        }

        return (int) $m[1] * 60 + (int) $m[2];
    }

    protected static function fmt(float $hours): string
    {
        return rtrim(rtrim(number_format($hours, 2, '.', ''), '0'), '.');
    }
}
