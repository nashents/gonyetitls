<?php

namespace App\Http\Livewire\Concerns;

/**
 * Standard "Auth Status" filter for index tables (pending / approved /
 * rejected ...). Blank = all. Pair with
 * @include('partials.auth-status-filter') in the view, call
 * applyAuthStatus() on the table query, and pass $this->auth_status to the
 * export so the download matches what's on screen.
 */
trait FiltersAuthStatus
{
    public $auth_status = '';

    public function updatedAuthStatus(): void
    {
        if (method_exists($this, 'resetPage')) {
            $this->resetPage();
        }
    }

    /** Options for the filter; override per component when a list has extra states. */
    public function authStatusOptions(): array
    {
        return [
            'pending' => 'Pending',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
        ];
    }

    /** Limit $query to the selected status; no-op when "All" is selected. */
    protected function applyAuthStatus($query, string $column = 'authorization')
    {
        return static::filterByAuthStatus($query, $this->auth_status, $column);
    }

    public static function filterByAuthStatus($query, $status, string $column = 'authorization')
    {
        if (blank($status)) {
            return $query;
        }

        // Legacy rows can carry a NULL status, which the app treats as pending.
        if ($status === 'pending') {
            return $query->where(function ($q) use ($column) {
                $q->where($column, 'pending')->orWhereNull($column);
            });
        }

        return $query->where($column, $status);
    }
}
