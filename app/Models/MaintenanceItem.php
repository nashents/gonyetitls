<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * A tracked workshop item with a fixed service/replacement interval, e.g.
 * THERMOSTATS @ 15000 HRS. Maintenance schedules (inspection_schedules,
 * type "maintenance") link a horse/vehicle/trailer/asset to an item.
 */
class MaintenanceItem extends Model implements Auditable
{
    use SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    public const UNITS = [
        'hours' => 'Hours',
        'km' => 'Km',
        'days' => 'Days',
    ];

    /** Equipment types an item can apply to; applies_to stores a comma list of these keys, or "all". */
    public const APPLIES_TO = [
        'horse' => 'Horses',
        'vehicle' => 'Vehicles',
        'trailer' => 'Trailers',
        'asset' => 'Assets',
    ];

    /** Unit suffix used in item labels and report cells. */
    public const UNIT_SUFFIX = [
        'hours' => 'HRS',
        'km' => 'KM',
        'days' => 'DAYS',
    ];

    protected $fillable = [
        'name', 'category', 'interval_unit', 'interval_hours', 'interval_km', 'interval_days',
        'warning_hours', 'warning_km', 'warning_days',
        'applies_to', 'is_monitoring', 'description', 'status', 'created_by',
    ];

    protected $casts = [
        'interval_hours' => 'float',
        'interval_km' => 'float',
        'interval_days' => 'float',
        'warning_hours' => 'float',
        'warning_km' => 'float',
        'warning_days' => 'float',
        'is_monitoring' => 'boolean',
        'status' => 'boolean',
    ];

    public function schedules()
    {
        return $this->hasMany(InspectionSchedule::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    /** Items that can be tracked on an equipment type ("Horse", "Trailer"...). */
    public function scopeForEquipment($query, ?string $equipment)
    {
        if (blank($equipment)) {
            return $query;
        }

        return $query->where(function ($q) use ($equipment) {
            $q->where('applies_to', 'all')
                ->orWhereRaw('FIND_IN_SET(?, applies_to)', [strtolower($equipment)]);
        });
    }

    /** ['horse', 'vehicle'] - every type when "all". */
    public function appliesToList(): array
    {
        if (blank($this->applies_to) || $this->applies_to === 'all') {
            return array_keys(self::APPLIES_TO);
        }

        return array_values(array_intersect(array_keys(self::APPLIES_TO), explode(',', $this->applies_to)));
    }

    public function appliesTo(?string $equipment): bool
    {
        return in_array(strtolower((string) $equipment), $this->appliesToList(), true);
    }

    /** Selected types => stored value: "all" when every type is ticked. */
    public static function encodeAppliesTo(array $types): string
    {
        $types = array_values(array_intersect(array_keys(self::APPLIES_TO), $types));

        return count($types) === count(self::APPLIES_TO) ? 'all' : implode(',', $types);
    }

    /** "Horses, Vehicles" / "All Equipment". */
    public function getAppliesToLabelAttribute(): string
    {
        $types = $this->appliesToList();

        return count($types) === count(self::APPLIES_TO)
            ? 'All Equipment'
            : implode(', ', array_map(fn ($t) => self::APPLIES_TO[$t], $types));
    }

    /** Units this item counts down in, e.g. ['hours', 'km'] - whichever runs out first. */
    public function units(): array
    {
        return self::parseUnits($this->interval_unit);
    }

    /** "hours,km" (or a legacy single unit) => ['hours', 'km'] in canonical order. */
    public static function parseUnits($value): array
    {
        $given = is_array($value) ? $value : explode(',', strtolower((string) $value));

        return array_values(array_intersect(array_keys(self::UNITS), array_map('trim', $given)));
    }

    public function intervalFor(string $unit): ?float
    {
        $value = $this->{"interval_{$unit}"} ?? null;

        return $value !== null && $value > 0 ? (float) $value : null;
    }

    /** "10000 HRS / 150000 KM" */
    public function intervalText(): string
    {
        return implode(' / ', array_map(
            fn ($unit) => self::formatNumber($this->intervalFor($unit)) . ' ' . self::UNIT_SUFFIX[$unit],
            $this->units()
        ));
    }

    /** "TURBO (10000 HRS / 150000 KM)" - the planner's column heading style. */
    public function getLabelAttribute(): string
    {
        return $this->name . ' (' . $this->intervalText() . ')' . ($this->is_monitoring ? ' MONITORING' : '');
    }

    /** Remaining at or below this is "Due Soon": the unit's warning value, else 10% of the interval. */
    public function warningThreshold(string $unit, ?float $interval = null): float
    {
        $warning = $this->{"warning_{$unit}"} ?? null;
        if ($warning !== null && $warning > 0) {
            return (float) $warning;
        }

        return round(($interval ?: $this->intervalFor($unit) ?: 0) * 0.1, 1);
    }

    public static function formatNumber($value): string
    {
        $value = (float) $value;

        return floor($value) == $value ? number_format($value, 0, '.', '') : rtrim(rtrim(number_format($value, 2, '.', ''), '0'), '.');
    }
}
