<?php

namespace App\Models\Concerns;

use Illuminate\Support\Arr;

/**
 * Horse / Trailer / Vehicle / Driver: which records may be picked in a
 * dropdown. Deactivated (deactivated_at set), archived (archive = 1) and
 * deleted (soft delete, excluded by default) records are hidden; for drivers
 * a suspended or archived employee also hides the driver.
 *
 * NOTE: `status` is NOT used: trips/breakdowns flip it to 0 while an asset is
 * busy ("Intransit"), so it means availability, not active/inactive.
 *
 * Edit forms pass the ids already saved on the record as $keep so the current
 * selection still renders even if it was deactivated since.
 */
trait SelectableAsset
{
    public function scopeSelectable($query, $keep = [])
    {
        $keep = array_values(array_filter(Arr::flatten(Arr::wrap($keep)), fn ($id) => filled($id)));
        $model = $this;

        return $query->where(function ($q) use ($model, $keep) {
            $q->where(function ($q) use ($model) {
                $q->whereNull($model->qualifyColumn('deactivated_at'))
                    ->where(function ($q) use ($model) {
                        $q->whereNull($model->qualifyColumn('archive'))
                            ->orWhere($model->qualifyColumn('archive'), 0);
                    });

                if (method_exists($model, 'employee')) {
                    $q->whereDoesntHave('employee', function ($e) {
                        $e->where(function ($e) {
                            $e->where('employees.status', 0)->orWhere('employees.archive', 1);
                        });
                    });
                }
            });

            if ($keep) {
                $q->orWhereIn($model->getQualifiedKeyName(), $keep);
            }
        });
    }

    public function isSelectable(): bool
    {
        if ($this->deactivated_at || (int) $this->archive === 1 || $this->trashed()) {
            return false;
        }

        if (method_exists($this, 'employee') && ($employee = $this->employee)) {
            return (int) $employee->status !== 0 && (int) $employee->archive !== 1;
        }

        return true;
    }

    public function isDeactivated(): bool
    {
        return $this->deactivated_at !== null;
    }
}
