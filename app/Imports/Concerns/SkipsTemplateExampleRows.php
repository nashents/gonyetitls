<?php

namespace App\Imports\Concerns;

/**
 * Every import template in public/templates ships with an example row whose
 * first value starts with "#". Laravel Excel calls isEmptyWhen() before
 * validation and before collection()/model(), so such rows are dropped
 * entirely — users can upload the template with the example still in it.
 *
 * ToModel imports must also implement SkipsEmptyRows for the hook to run;
 * ToCollection/ToArray imports get it regardless.
 */
trait SkipsTemplateExampleRows
{
    public function isEmptyWhen(array $row): bool
    {
        return $this->isTemplateExampleRow($row);
    }

    protected function isTemplateExampleRow(array $row): bool
    {
        foreach ($row as $value) {
            if ($value === null || (is_string($value) && trim($value) === '')) {
                continue;
            }

            // The first filled cell decides: "#..." marks the example row.
            return is_string($value) && str_starts_with(ltrim($value), '#');
        }

        return false;
    }
}
