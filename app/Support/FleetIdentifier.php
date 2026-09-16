<?php

namespace App\Support;

use App\Models\Company;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

/**
 * Resolves the company's preference for identifying horses/trailers/vehicles:
 * by Fleet Number or by Vehicle Registration Number (VRN). Governs both the
 * combined display label ("primary (secondary)") and the sort column used
 * across fleet listings.
 *
 * Preference is resolved per fleet model instance via its owning user's
 * company (Horse/Trailer/Vehicle::user_id) rather than the signed-in user,
 * so it resolves correctly both in authenticated web requests and in
 * unauthenticated console/cron contexts (scheduled report emails, exports
 * triggered by artisan commands, queued jobs, etc.) where Auth::user() is
 * null. Falling back to Auth::user()'s company only covers call sites with
 * no model instance to key off, e.g. the query sort scope.
 */
class FleetIdentifier
{
    const REGISTRATION_NUMBER = 'registration_number';
    const FLEET_NUMBER = 'fleet_number';

    protected static array $companyCache = [];

    public static function companyFor($model = null): ?Company
    {
        $userId = $model->user_id ?? null;

        if ($userId) {
            if (! array_key_exists($userId, self::$companyCache)) {
                $owner = User::find($userId);
                self::$companyCache[$userId] = $owner
                    ? ($owner->company ?? optional($owner->employee)->company)
                    : null;
            }

            if (self::$companyCache[$userId]) {
                return self::$companyCache[$userId];
            }
        }

        $authUser = Auth::user();

        if ($authUser) {
            return $authUser->company ?? optional($authUser->employee)->company;
        }

        return null;
    }

    public static function preference($model = null): string
    {
        $preference = optional(self::companyFor($model))->fleet_identifier_preference;

        return $preference === self::FLEET_NUMBER
            ? self::FLEET_NUMBER
            : self::REGISTRATION_NUMBER;
    }

    public static function primaryColumn($model = null): string
    {
        return self::preference($model);
    }

    public static function secondaryColumn($model = null): string
    {
        return self::preference($model) === self::FLEET_NUMBER
            ? self::REGISTRATION_NUMBER
            : self::FLEET_NUMBER;
    }

    public static function label($model): string
    {
        $primaryColumn = self::primaryColumn($model);
        $secondaryColumn = self::secondaryColumn($model);
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

    /**
     * Workshop-style equipment label: "{identifier} {make} {model}", e.g.
     * "H23 (ADK4990) Volvo FH16" — used alongside a separate "Equipment Type"
     * column (Horse/Vehicle/Trailer) by booking/ticket/reminder exports.
     */
    public static function workshopLabel($model, ?string $make, ?string $modelName): string
    {
        $parts = array_filter([self::label($model), $make, $modelName], fn ($value) => filled($value));

        return trim(implode(' ', $parts));
    }
}
