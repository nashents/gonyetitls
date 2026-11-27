<?php

namespace App\Http\Livewire\Concerns;

use App\Models\Employee;
use Illuminate\Validation\ValidationException;

/**
 * Employees / Drivers Create forms: refuse to create a second record for a
 * person who already exists (same ID number, or same name with no
 * conflicting ID number). Duplicates otherwise show up twice on attendance
 * registers, payroll, etc. Uses $name, $surname, $idnumber on the component.
 */
trait PreventsDuplicateEmployees
{
    protected function assertNotDuplicateEmployee(): void
    {
        $existing = Employee::findExisting($this->name, $this->surname, $this->idnumber ?? null);

        if (!$existing) {
            return;
        }

        $sameId = filled($this->idnumber ?? null)
            && Employee::normalizeIdNumber($existing->idnumber) === Employee::normalizeIdNumber($this->idnumber);

        $message = $sameId
            ? "ID number {$this->idnumber} already belongs to {$existing->duplicateLabel()}. Open that record instead of creating a new one."
            : "{$existing->duplicateLabel()} already exists. Open that record instead"
                . ($existing->archive ? ' (unarchive it if they are back)' : '')
                . ". If this is a different person with the same name, enter their ID number.";

        $field = $sameId ? 'idnumber' : 'name';
        $this->addError($field, $message);

        $this->dispatchBrowserEvent('alert', ['type' => 'error', 'message' => $message]);

        throw ValidationException::withMessages([$field => $message]);
    }
}
