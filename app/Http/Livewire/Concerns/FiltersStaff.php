<?php

namespace App\Http\Livewire\Concerns;

use App\Models\Branch;
use App\Models\Department;
use App\Models\Rank;
use App\Models\Role;

/**
 * Role / rank / department / branch filters for the Employees, Drivers and
 * Leave Days lists (view: includes.staff-filters). Pass staffFilters() to
 * Employee::scopeFilterBy() for the list AND the exports so they always match.
 */
trait FiltersStaff
{
    public $filter_role_id;
    public $filter_rank_id;
    public $filter_department_id;
    public $filter_branch_id;

    protected $queryStringFiltersStaff = [
        'filter_role_id'       => ['except' => ''],
        'filter_rank_id'       => ['except' => ''],
        'filter_department_id' => ['except' => ''],
        'filter_branch_id'     => ['except' => ''],
    ];

    public function staffFilters(): array
    {
        return [
            'role_id'       => $this->filter_role_id,
            'rank_id'       => $this->filter_rank_id,
            'department_id' => $this->filter_department_id,
            'branch_id'     => $this->filter_branch_id,
        ];
    }

    public function hasStaffFilters(): bool
    {
        return !empty(array_filter($this->staffFilters(), 'filled'));
    }

    public function clearStaffFilters(): void
    {
        $this->reset(['filter_role_id', 'filter_rank_id', 'filter_department_id', 'filter_branch_id']);
        // Leave Days shows its all / drivers / admin select in the same filter row.
        if (property_exists($this, 'staff_type')) {
            $this->reset('staff_type');
        }
        $this->resetPage();
    }

    public function updatingFiltersStaff($name): void
    {
        if (str_starts_with($name, 'filter_')) {
            $this->resetPage();
        }
    }

    /** Dropdown options, as $this->staffFilterOptions in the view. */
    public function getStaffFilterOptionsProperty(): array
    {
        return [
            'roles'       => Role::orderBy('name')->get(['id', 'name']),
            'ranks'       => Rank::orderBy('name')->get(['id', 'name']),
            'departments' => Department::orderBy('name')->get(['id', 'name']),
            'branches'    => Branch::orderBy('name')->get(['id', 'name']),
        ];
    }

    /** Export file name suffix, e.g. "_filtered", so filtered files are told apart. */
    protected function staffFilterFileSuffix(): string
    {
        return $this->hasStaffFilters() ? '_filtered' : '';
    }
}
