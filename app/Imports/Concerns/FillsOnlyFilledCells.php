<?php

namespace App\Imports\Concerns;

use Illuminate\Database\Eloquent\Model;

/**
 * Re-uploading an import file updates existing records: a blank cell means
 * "leave as is", never "clear it". Null / whitespace-only values are skipped.
 */
trait FillsOnlyFilledCells
{
    protected function fillFilled(Model $model, array $attributes): void
    {
        foreach ($attributes as $attribute => $value) {
            if ($value === null || (is_string($value) && trim($value) === '')) {
                continue;
            }

            $model->{$attribute} = is_string($value) ? trim($value) : $value;
        }
    }
}
