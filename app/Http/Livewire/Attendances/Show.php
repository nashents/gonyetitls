<?php

namespace App\Http\Livewire\Attendances;

use Livewire\Component;
use App\Models\Attendance;

class Show extends Component
{
    public $attendance;

    public function mount($id){
        $this->attendance = Attendance::findOrFail($id);
    }

    /**
     * Every employee in the register's department, marked or not, so a skipped
     * person shows as a blank row and can be followed up with whoever captured
     * the register. register = null means not marked.
     */
    protected function rows()
    {
        $marked = $this->attendance->attendance_registers()->with('employee')->get()->keyBy('employee_id');

        $rows = $this->attendance->expectedEmployees()->map(fn ($employee) => (object) [
            'employee' => $employee,
            'register' => $marked->get($employee->id),
        ]);

        // Marked people no longer in the department (moved/archived since) still show.
        $listed = $rows->pluck('employee.id')->all();
        foreach ($marked as $employeeId => $register) {
            if (!in_array($employeeId, $listed)) {
                $rows->push((object) ['employee' => $register->employee, 'register' => $register]);
            }
        }

        return $rows->values();
    }

    public function render()
    {
        $rows = $this->rows();

        return view('livewire.attendances.show', [
            'attendance_registers' => $rows,
            'otherKindRecords' => Attendance::otherKindRecords($rows->pluck('employee')),
            'marked_count' => $rows->filter(fn ($r) => Attendance::isMarked($r->register))->count(),
            'unmarked_count' => $rows->reject(fn ($r) => Attendance::isMarked($r->register))->count(),
        ]);
    }
}
