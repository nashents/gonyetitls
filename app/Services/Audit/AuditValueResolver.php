<?php

namespace App\Services\Audit;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Throwable;

/**
 * Turns raw audit ids into something a person can investigate with:
 * "43 (John Moyo)", "12 (ATT00012)", "7 (INV-0007)".
 *
 * Foreign keys (*_id) are resolved through the audited model's belongsTo
 * relation of the same name (employee_id -> employee()), falling back to
 * App\Models\<Studly>. Deleted records are still labelled (withTrashed).
 * Lookups are cached for the request.
 */
class AuditValueResolver
{
    /** @var array<string, array<int|string, string|null>> */
    protected array $cache = [];

    /** Readable label for one record of $class, or null if unknown. */
    public function label(?string $class, $id): ?string
    {
        if (!$class || blank($id) || !class_exists($class)) {
            return null;
        }

        if (!array_key_exists((string) $id, $this->cache[$class] ?? [])) {
            $this->prime($class, [$id]);
        }

        return $this->cache[$class][(string) $id] ?? null;
    }

    /** Loads labels for many ids of one class in a single query. */
    public function prime(string $class, array $ids): void
    {
        $ids = array_values(array_unique(array_filter($ids, fn ($id) => filled($id) && is_scalar($id))));
        $missing = array_values(array_filter($ids, fn ($id) => !array_key_exists((string) $id, $this->cache[$class] ?? [])));
        if (!$missing || !class_exists($class) || !is_subclass_of($class, Model::class)) {
            return;
        }

        foreach ($missing as $id) {
            $this->cache[$class][(string) $id] = null;
        }

        try {
            $query = $class::query();
            if (in_array(SoftDeletes::class, class_uses_recursive($class), true)) {
                $query->withTrashed();
            }
            foreach ($query->whereKey($missing)->get() as $model) {
                $this->cache[$class][(string) $model->getKey()] = $this->describe($model);
            }
        } catch (Throwable $e) {
            // Unreadable table/model: leave the raw id.
        }
    }

    /** Model class a foreign-key field on $auditableClass points to. */
    public function relatedClass(?string $auditableClass, string $field): ?string
    {
        if (!Str::endsWith($field, '_id') || $field === 'id') {
            return null;
        }

        $base = Str::beforeLast($field, '_id');

        if ($auditableClass && class_exists($auditableClass)) {
            foreach (array_unique([$base, Str::camel($base)]) as $method) {
                if (!method_exists($auditableClass, $method)) {
                    continue;
                }
                try {
                    $relation = (new $auditableClass)->{$method}();
                    if ($relation instanceof BelongsTo && $relation->getForeignKeyName() === $field) {
                        return get_class($relation->getRelated());
                    }
                } catch (Throwable $e) {
                    // Not a relation, or needs arguments.
                }
            }
        }

        $guess = 'App\\Models\\' . Str::studly($base);

        return class_exists($guess) ? $guess : null;
    }

    /**
     * Field/old/new rows for an audit, with ids labelled:
     * [['field' => 'employee_id', 'old' => null, 'new' => '43 (John Moyo)'], ...]
     */
    public function changes($audit): array
    {
        $old = (array) ($audit->old_values ?? []);
        $new = (array) ($audit->new_values ?? []);
        $type = $audit->auditable_type;

        $rows = [];
        foreach (array_unique(array_merge(array_keys($old), array_keys($new))) as $field) {
            $rows[] = [
                'field' => $field,
                'old' => $this->formatValue($type, $field, $old[$field] ?? null, array_key_exists($field, $old)),
                'new' => $this->formatValue($type, $field, $new[$field] ?? null, array_key_exists($field, $new)),
            ];
        }

        return $rows;
    }

    protected function formatValue(?string $type, string $field, $value, bool $present): ?string
    {
        if (!$present) {
            return null;
        }
        if ($value === null || $value === '') {
            return '(blank)';
        }
        if (is_array($value) || is_object($value)) {
            return json_encode($value);
        }

        $related = $this->relatedClass($type, $field);
        $label = $related ? $this->label($related, $value) : null;

        return $label ? "{$value} ({$label})" : (string) $value;
    }

    /** Best human identifier for a record. */
    public function describe(Model $model): ?string
    {
        // Attendance rows: who, and on which register.
        if ($model instanceof \App\Models\AttendanceRegister) {
            $employee = $model->employee()->withoutGlobalScopes()->first();
            $register = $model->attendance_id ? $model->attendance()->withoutGlobalScopes()->first() : null;

            return trim(($employee ? trim($employee->name . ' ' . $employee->surname) : 'Employee #' . $model->employee_id)
                . ' on ' . ($register->attendance_number ?? 'no register'));
        }

        $attributes = $model->getAttributes();

        // Horses / trailers / vehicles: "AAZ 0790 (H12)".
        if (filled($attributes['registration_number'] ?? null) || filled($attributes['fleet_number'] ?? null)) {
            $label = \App\Support\FleetIdentifier::regFleetLabel($model);
            if (filled($label)) {
                return $label;
            }
        }

        // Document numbers: attendance_number, invoice_number, trip_number...
        $number = null;
        foreach ($attributes as $key => $value) {
            if (Str::endsWith($key, '_number') && filled($value)
                && !in_array($key, ['registration_number', 'fleet_number', 'phone_number', 'id_number', 'account_number'], true)) {
                $number = (string) $value;
                break;
            }
        }

        // People: "John Moyo (FHE00004)".
        if ($number && filled($attributes['name'] ?? null) && array_key_exists('surname', $attributes)) {
            return trim($attributes['name'] . ' ' . $attributes['surname']) . " ({$number})";
        }
        if ($number) {
            return $number;
        }

        if (filled($attributes['name'] ?? null)) {
            return trim($attributes['name'] . ' ' . ($attributes['surname'] ?? ''));
        }

        foreach (['title', 'reference', 'code', 'email', 'serial_number'] as $key) {
            if (filled($attributes[$key] ?? null)) {
                return (string) $attributes[$key];
            }
        }

        return null;
    }
}
