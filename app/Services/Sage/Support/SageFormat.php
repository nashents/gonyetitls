<?php

namespace App\Services\Sage\Support;

use Carbon\Carbon;
use Throwable;

/**
 * Small formatting helpers shared by the Sage mappers.
 */
class SageFormat
{
    /**
     * Sanitize a value into a stable Sage CLASSID/PROJECTID: trim, collapse
     * inner whitespace, uppercase, strip characters Sage dislikes, and cap
     * length. The result is stored in the mapping so it never drifts.
     */
    public static function id(?string $value, int $maxLength = 20): string
    {
        $value = (string) $value;
        $value = preg_replace('/\s+/', ' ', trim($value));
        // Keep alphanumerics, space, dash, dot, slash, underscore.
        $value = preg_replace('/[^A-Za-z0-9 \-\.\/_]/', '', $value);
        $value = strtoupper($value);

        return mb_substr($value, 0, $maxLength);
    }

    /** Gonyeti stores status as 1/0 (or true/false); Sage wants active/inactive. */
    public static function boolStatus($status): string
    {
        if (is_bool($status)) {
            return $status ? 'active' : 'inactive';
        }
        return (string) $status === '0' ? 'inactive' : 'active';
    }

    /**
     * Format a date value as Sage's mm/dd/yyyy. Returns null when the value is
     * empty or unparseable (so the field is simply omitted).
     */
    public static function date($value): ?string
    {
        if (empty($value)) {
            return null;
        }
        try {
            return Carbon::parse($value)->format('m/d/Y');
        } catch (Throwable $e) {
            return null;
        }
    }
}
