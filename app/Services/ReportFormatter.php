<?php

namespace App\Services;

/**
 * Shared number formatting for the Wave-style financial report exports
 * (print + PDF), so every report renders amounts/percentages identically.
 */
class ReportFormatter
{
    /**
     * Whether a value from one of this app's many string-typed "numeric"
     * columns (total, balance, subtotal, tax_amount, etc.) is actually a
     * well-formed number and safe to cast/sum, rather than dirty data.
     */
    public static function isNumericAmount($value): bool
    {
        return is_string($value) && preg_match('/^-?[0-9]+(\.[0-9]+)?$/', trim($value)) === 1;
    }

    /**
     * Format a monetary amount using accounting convention:
     * negative values are wrapped in parentheses.
     */
    public static function money($value): string
    {
        $value = is_numeric($value) ? (float) $value : 0.0;
        $formatted = number_format(abs($value), 2);

        return $value < 0 ? "({$formatted})" : $formatted;
    }

    public static function percent($value): string
    {
        return is_numeric($value) ? number_format((float) $value, 2) . '%' : '0.00%';
    }

    /**
     * Format a monetary amount the way Wave's exports do: a leading minus
     * sign (not parentheses) before the currency code, and trailing zero
     * decimals trimmed off (8,922,144.50 -> 8,922,144.5, 0.00 -> 0).
     */
    public static function waveMoney($value, string $currencyCode = ''): string
    {
        $value = is_numeric($value) ? (float) $value : 0.0;
        $formatted = rtrim(rtrim(number_format(abs($value), 2), '0'), '.');

        return ($value < 0 ? '-' : '') . $currencyCode . $formatted;
    }
}
