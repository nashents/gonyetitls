<?php

namespace App\Support;

use Illuminate\Support\Facades\Auth;

/**
 * Resolves the company's preference for identifying horses/trailers/vehicles:
 * by Fleet Number or by Vehicle Registration Number (VRN). Governs both the
 * combined display label ("primary (secondary)") and the sort column used
 * across fleet listings.
 */
class FleetIdentifier
{
    const REGISTRATION_NUMBER = 'registration_number';
    const FLEET_NUMBER = 'fleet_number';

    public static function preference(): string
    {
        $user = Auth::user();

        if (! $user) {
            return self::REGISTRATION_NUMBER;
        }

        $company = $user->company ?? optional($user->employee)->company ?? null;
        $preference = $company->fleet_identifier_preference ?? null;

        return $preference === self::FLEET_NUMBER
            ? self::FLEET_NUMBER
            : self::REGISTRATION_NUMBER;
    }

    public static function primaryColumn(): string
    {
        return self::preference();
    }

    public static function secondaryColumn(): string
    {
        return self::preference() === self::FLEET_NUMBER
            ? self::REGISTRATION_NUMBER
            : self::FLEET_NUMBER;
    }

    public static function label($model): string
    {
        $primaryColumn = self::primaryColumn();
        $secondaryColumn = self::secondaryColumn();
        $primary = self::format($primaryColumn, $model->{$primaryColumn});
        $secondary = self::format($secondaryColumn, $model->{$secondaryColumn});

        if (blank($primary)) {
            return (string) $secondary;
        }

        return blank($secondary) ? $primary : "{$primary} ({$secondary})";
    }

    protected static function format(string $column, $value): ?string
    {
        if (blank($value)) {
            return null;
        }

        return $column === self::REGISTRATION_NUMBER ? ucfirst($value) : $value;
    }
}
