<?php

namespace App\Http\Livewire\Concerns;

use App\Models\Employee;

/**
 * Multi-department picker with a default flag for the Change Position modal
 * (Employees\Index and Drivers\Index). View: livewire.partials.position-departments.
 */
trait ManagesPositionDepartments
{
    /** Every department the employee belongs to. */
    public $department_ids = [];
    /** Which of $department_ids is the default one. */
    public $default_department_id;
    /** The "add a department" dropdown. */
    public $department_picker = '';

    /** Picking a department in the dropdown adds it to the list. */
    public function updatedDepartmentPicker($value)
    {
        $id = (int) $value;
        if ($id && ! in_array($id, $this->department_ids)) {
            $this->department_ids[] = $id;
            if (! $this->default_department_id) {
                $this->default_department_id = $id;
            }
        }
        $this->department_picker = '';
    }

    public function removeDepartment($id)
    {
        $id = (int) $id;
        $this->department_ids = array_values(array_filter($this->department_ids, fn ($d) => (int) $d !== $id));
        if ((int) $this->default_department_id === $id) {
            $this->default_department_id = $this->department_ids[0] ?? null;
        }
    }

    protected function resetPositionDepartments(): void
    {
        $this->department_ids = [];
        $this->default_department_id = null;
        $this->department_picker = '';
    }

    /**
     * Load the employee's actual memberships (with the flagged default);
     * $fallbackDepartmentId (the position's department) is used only when
     * the employee has none.
     */
    protected function loadPositionDepartments(Employee $employee, $fallbackDepartmentId = null): void
    {
        $this->resetErrorBag('department_ids');
        $this->department_picker = '';
        $this->department_ids = $employee->departments()
            ->orderBy('department_employee.id')->pluck('departments.id')
            ->map(fn ($d) => (int) $d)->all();
        $this->default_department_id = $employee->defaultDepartment()?->id;

        if (empty($this->department_ids) && $fallbackDepartmentId) {
            $this->department_ids = [(int) $fallbackDepartmentId];
            $this->default_department_id = (int) $fallbackDepartmentId;
        }
    }

    /**
     * Validates the selection. Returns [department ids, default id], or null
     * (with an error added) when departments are listed without a default.
     */
    protected function validatedPositionDepartments(): ?array
    {
        $ids = array_values(array_unique(array_map('intval', $this->department_ids ?? [])));
        $default = (int) $this->default_department_id ?: null;

        if ($ids && ! in_array($default, $ids)) {
            $this->addError('department_ids', 'Choose which department is the default one.');
            return null;
        }

        return [$ids, $ids ? $default : null];
    }

    /** Replace the employee's departments with the selection and flag the default. */
    protected function syncPositionDepartments(Employee $employee, array $ids, ?int $default): void
    {
        $employee->departments()->sync($ids);
        if ($default) {
            $employee->setDefaultDepartment($default);
        }
        $employee->ensureDefaultDepartment();
    }
}
