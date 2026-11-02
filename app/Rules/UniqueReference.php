<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

/**
 * A user-entered reference (trip ref, custom ref, deal reference...) must not
 * already be used by another live record. Comparison ignores case and
 * surrounding spaces, so "ABC 1" and " abc 1" count as the same reference.
 * Soft-deleted rows don't block re-use; blank values always pass.
 */
class UniqueReference implements Rule
{
    protected $model;
    protected $column;
    protected $ignoreId;
    protected $label;

    public function __construct(string $model, string $column, $ignoreId = null, string $label = 'reference')
    {
        $this->model = $model;
        $this->column = $column;
        $this->ignoreId = $ignoreId;
        $this->label = $label;
    }

    public static function exists(string $model, string $column, $value, $ignoreId = null): bool
    {
        $value = trim((string) $value);
        if ($value === '') {
            return false;
        }

        return $model::query()
            ->whereRaw('LOWER(TRIM(`'.$column.'`)) = ?', [mb_strtolower($value)])
            ->when($ignoreId, fn ($q) => $q->where((new $model)->getKeyName(), '!=', $ignoreId))
            ->exists();
    }

    public function passes($attribute, $value)
    {
        return ! static::exists($this->model, $this->column, $value, $this->ignoreId);
    }

    public function message()
    {
        return 'This '.$this->label.' is already in use. Please enter a unique '.$this->label.'.';
    }
}
