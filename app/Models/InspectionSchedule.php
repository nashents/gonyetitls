<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * Inspection (type "inspection") and maintenance (type "maintenance")
 * schedules for one horse/vehicle/trailer/asset. A maintenance schedule may
 * track a MaintenanceItem (THERMOSTATS @ 15000 HRS...) by hour meter, km or
 * days; remaining() / dueStatus() give what's left against the unit's
 * current hours/mileage or today's date.
 */
class InspectionSchedule extends Model implements Auditable
{
    use Concerns\GuardsOperationalAssets;
    use HasFactory;
    use SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    /**
     * trigger_type holds the units counted, as a comma list ("hours,km");
     * a schedule is due when ANY of them runs out. Older rows hold one of
     * these legacy values.
     */
    public const LEGACY_TRIGGERS = [
        'hours' => ['hours'],
        'mileage' => ['km'],
        'date' => ['days'],
        'both' => ['km', 'days'],
    ];

    public const STATUS_LABELS = [
        'overdue' => 'Overdue',
        'due_soon' => 'Due Soon',
        'ok' => 'OK',
        'no_reading' => 'No Reading',
    ];

    /** Worst first - the overall status is the worst of its dimensions. */
    protected const STATUS_RANK = ['overdue' => 0, 'due_soon' => 1, 'no_reading' => 2, 'ok' => 3];

    protected $casts = [
        'last_inspection_date' => 'date',
        'next_due_date' => 'date',
    ];

    public function horse()
    {
        return $this->belongsTo(Horse::class);
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function trailer()
    {
        return $this->belongsTo(Trailer::class);
    }

    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }

    public function maintenance_item()
    {
        return $this->belongsTo(MaintenanceItem::class)->withTrashed();
    }

    public function service_type()
    {
        return $this->belongsTo(ServiceType::class);
    }

    public function problem_category()
    {
        return $this->belongsTo(ProblemCategory::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeMaintenance($query)
    {
        return $query->where('type', 'maintenance');
    }

    public function scopeActive($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('status')->orWhere('status', 'active');
        });
    }

    public function equipment()
    {
        return $this->horse ?? $this->vehicle ?? $this->trailer ?? $this->asset;
    }

    public function equipmentType(): ?string
    {
        if ($this->horse_id) return 'Horse';
        if ($this->vehicle_id) return 'Vehicle';
        if ($this->trailer_id) return 'Trailer';
        if ($this->asset_id) return 'Asset';

        return null;
    }

    public function equipmentLabel(): string
    {
        $equipment = $this->equipment();

        return $equipment ? self::labelFor($equipment) : '';
    }

    public static function labelFor($equipment): string
    {
        if ($equipment instanceof Asset) {
            return trim(($equipment->product->name ?? 'Asset') . ($equipment->serial_number ? ' SN:' . $equipment->serial_number : ''));
        }

        // Always "AAZ 0790 (H12)"; the company setting only affects ordering.
        return \App\Support\FleetIdentifier::regFleetLabel($equipment)
            ?: (string) ($equipment->identifier_label ?: $equipment->registration_number);
    }

    public function scheduleFor(): string
    {
        if ($this->maintenance_item) return $this->maintenance_item->name;
        if ($this->service_type) return $this->service_type->name;
        if ($this->problem_category) return $this->problem_category->name;

        return (string) $this->component;
    }

    /** Dimensions this schedule counts down in: hours, km and/or days. */
    public function dimensions(): array
    {
        return self::unitsFromTrigger($this->trigger_type) ?: ['km', 'days'];
    }

    /** trigger_type ("hours,km" or a legacy value) => ['hours', 'km']. */
    public static function unitsFromTrigger($trigger): array
    {
        $trigger = strtolower(trim((string) $trigger));

        return self::LEGACY_TRIGGERS[$trigger] ?? MaintenanceItem::parseUnits($trigger);
    }

    /** "Hours or Km" for lists. */
    public function triggerText(): string
    {
        return implode(' or ', array_map(fn ($u) => MaintenanceItem::UNITS[$u], $this->dimensions()));
    }

    public function intervalFor(string $unit): ?float
    {
        $value = match ($unit) {
            'hours' => $this->interval_hours,
            'km' => $this->interval_km,
            default => $this->interval_days,
        };

        return is_numeric($value) ? (float) $value : null;
    }

    /** Unit's current hour meter / odometer; null when not captured. */
    public static function currentReading($equipment, string $unit): ?float
    {
        if (!$equipment || $equipment instanceof Asset) {
            return null;
        }

        $value = $unit === 'hours' ? $equipment->hours : $equipment->mileage;
        $value = is_string($value) ? str_replace([',', ' '], '', $value) : $value;

        return is_numeric($value) && (float) $value > 0 ? (float) $value : null;
    }

    /**
     * Blank "last done" defaults to the unit's current details: today's date,
     * and its current hour meter / odometer for each counted dimension (left
     * blank when the unit has no reading captured). Call before
     * recalculateNextDue() so next due = current + interval.
     */
    public function fillBlankLastDone(): void
    {
        $this->unsetRelations();
        $equipment = $this->equipment();
        $dims = $this->dimensions();

        if (!$this->last_inspection_date) {
            $this->last_inspection_date = now()->toDateString();
        }
        if (in_array('hours', $dims) && !is_numeric($this->last_inspection_hours)) {
            $this->last_inspection_hours = self::currentReading($equipment, 'hours');
        }
        if (in_array('km', $dims) && !is_numeric($this->last_inspection_km)) {
            $km = self::currentReading($equipment, 'km');
            $this->last_inspection_km = $km !== null ? (int) round($km) : null;
        }
    }

    /** next_due_* from last done + interval (only where both are known). */
    public function recalculateNextDue(): void
    {
        if (is_numeric($this->last_inspection_hours) && $this->interval_hours) {
            $this->next_due_hours = (float) $this->last_inspection_hours + (float) $this->interval_hours;
        }
        if (is_numeric($this->last_inspection_km) && $this->interval_km) {
            $this->next_due_km = (int) round((float) $this->last_inspection_km + (float) $this->interval_km);
        }
        if ($this->last_inspection_date && $this->interval_days) {
            $this->next_due_date = Carbon::parse($this->last_inspection_date)->addDays((int) $this->interval_days)->toDateString();
        }
    }

    /**
     * Remaining per dimension: ['hours' => ['remaining' => 1250.0, 'status' => 'ok', ...], ...].
     * remaining is null when there is no next due or no current reading.
     */
    public function remaining(?Carbon $asAt = null): array
    {
        $asAt = ($asAt ?? now())->copy()->startOfDay();
        $equipment = $this->equipment();
        $out = [];

        foreach ($this->dimensions() as $unit) {
            $current = $unit === 'days' ? null : self::currentReading($equipment, $unit);
            $nextDue = match ($unit) {
                'hours' => is_numeric($this->next_due_hours) ? (float) $this->next_due_hours : null,
                'km' => is_numeric($this->next_due_km) ? (float) $this->next_due_km : null,
                default => $this->next_due_date,
            };

            $remaining = null;
            if ($unit === 'days') {
                $remaining = $nextDue ? (float) $asAt->diffInDays(Carbon::parse($nextDue)->startOfDay(), false) : null;
            } elseif ($nextDue !== null && $current !== null) {
                $remaining = round($nextDue - $current, 1);
            }

            $out[$unit] = [
                'current' => $current,
                'next_due' => $nextDue,
                'interval' => $this->intervalFor($unit),
                'remaining' => $remaining,
                'status' => $this->statusFor($unit, $remaining),
            ];
        }

        return $out;
    }

    protected function statusFor(string $unit, ?float $remaining): string
    {
        if ($remaining === null) {
            return 'no_reading';
        }
        if ($remaining <= 0) {
            return 'overdue';
        }

        $interval = $this->intervalFor($unit);
        $threshold = $this->maintenance_item
            ? $this->maintenance_item->warningThreshold($unit, $interval)
            : round(($interval ?: 0) * 0.1, 1);

        return $remaining <= $threshold ? 'due_soon' : 'ok';
    }

    /**
     * The unit closest to running out (worst status, then the smallest share
     * of its interval left) - what the planner grid shows for a multi-unit item.
     * Returns [unit, row] or [null, null].
     */
    public function leadingDimension(?Carbon $asAt = null): array
    {
        $rows = $this->remaining($asAt);
        if (!$rows) {
            return [null, null];
        }

        $share = fn ($row) => $row['remaining'] === null ? INF
            : ($row['interval'] ? $row['remaining'] / $row['interval'] : $row['remaining']);

        uksort($rows, function ($a, $b) use ($rows, $share) {
            return [self::STATUS_RANK[$rows[$a]['status']] ?? 9, $share($rows[$a])]
                <=> [self::STATUS_RANK[$rows[$b]['status']] ?? 9, $share($rows[$b])];
        });

        $unit = array_key_first($rows);

        return [$unit, $rows[$unit]];
    }

    /** Worst status across dimensions. */
    public function dueStatus(?Carbon $asAt = null): string
    {
        return self::worstStatus(array_column($this->remaining($asAt), 'status'));
    }

    public static function worstStatus(array $statuses): string
    {
        if (!$statuses) {
            return 'no_reading';
        }

        usort($statuses, fn ($a, $b) => (self::STATUS_RANK[$a] ?? 9) <=> (self::STATUS_RANK[$b] ?? 9));

        return $statuses[0];
    }

    /** "1,250 HRS | 12 DAYS" - remaining per dimension for lists and the report. */
    public function remainingText(?Carbon $asAt = null): string
    {
        $parts = [];
        foreach ($this->remaining($asAt) as $unit => $row) {
            $parts[] = $row['remaining'] === null
                ? '- ' . MaintenanceItem::UNIT_SUFFIX[$unit]
                : number_format($row['remaining'], floor($row['remaining']) == $row['remaining'] ? 0 : 1) . ' ' . MaintenanceItem::UNIT_SUFFIX[$unit];
        }

        return implode(' | ', $parts);
    }
}
