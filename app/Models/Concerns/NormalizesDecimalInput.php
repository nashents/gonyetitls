<?php

namespace App\Models\Concerns;

/**
 * Cleans raw form input before it reaches a `decimal:N` cast.
 *
 * Laravel's decimal cast calls number_format() on read, which throws a
 * TypeError for "" or "1,000". Blank becomes null, thousands separators and
 * spaces are stripped, and anything still non-numeric is stored as null.
 */
trait NormalizesDecimalInput
{
    public function setAttribute($key, $value)
    {
        if (in_array($key, $this->normalizedDecimalAttributes(), true)) {
            $value = static::normalizeDecimalInput($value);
        }

        return parent::setAttribute($key, $value);
    }

    protected function normalizedDecimalAttributes(): array
    {
        return array_keys(array_filter(
            $this->getCasts(),
            fn ($cast) => is_string($cast) && str_starts_with($cast, 'decimal:')
        ));
    }

    public static function normalizeDecimalInput($value)
    {
        if ($value === null || is_int($value) || is_float($value)) {
            return $value;
        }

        $value = str_replace([',', ' '], '', trim((string) $value));

        return is_numeric($value) ? $value : null;
    }
}
