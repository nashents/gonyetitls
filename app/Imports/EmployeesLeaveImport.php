<?php

namespace App\Imports;

use App\Models\Employee;
use App\Models\EmployeeLeave;
use App\Models\LeaveType;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithLimit;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;

class EmployeesLeaveImport implements  ToCollection, SkipsEmptyRows, WithLimit,
WithHeadingRow,
SkipsOnError,
WithValidation,
WithChunkReading,
WithBatchInserts,
WithCalculatedFormulas
{
    use Importable, SkipsErrors;
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */

     public function limit(): int
    {
        return 2500; // Import only the first 100 rows
    }
    public function collection(Collection $rows)
    {
       foreach($rows as $row){
        if($row->filter()->isNotEmpty()){

        $employeeNumber = trim($row['employee_number'] ?? '');
        $fullname = strtolower(trim($row['fullname'] ?? ''));

        if (!$employeeNumber && !$fullname) {
            continue;
        }

        $employee = Employee::query()
            ->when($employeeNumber, function ($query) use ($employeeNumber) {
                $query->where('employee_number', $employeeNumber);
            })
            ->when(!$employeeNumber && $fullname, function ($query) use ($fullname) {
                $query->whereRaw(
                    "LOWER(TRIM(CONCAT(name, ' ', surname))) = ?",
                    [$fullname]
                );
            })
            ->first();
          

        if (!$employee) {
            continue;
        }

        $leaveType = LeaveType::whereRaw('LOWER(TRIM(name)) = ?', [strtolower(trim($row['leave_type'] ?? ''))])->first();
  

        if (!$leaveType) {
            continue;
        }
      

        EmployeeLeave::updateOrCreate(
            [
                'employee_id' => $employee->id,
                'leave_type_id' => $leaveType->id,
            ],
            [
                'acrual_rate' => $row['accrual_rate'],
                'available_leave_days' => $row['available_leave_days'],
                'maximum_leave_days' => $row['maximum_leave_days'],
            ]
        );

        // Backward compatibility: keep employee table updated for Annual Leave
        if (strtolower(trim($leaveType->name)) === 'annual') {
            $employee->accrual_rate = $row['accrual_rate'];
            $employee->leave_days = $row['available_leave_days'];
            $employee->maximum_leave_days = $row['maximum_leave_days'];
            $employee->update();
        }

    }
       }
    }

    public function rules(): array{
        return[
            // '*.accrual_rate' => ['required'],
            // '*.leave_days' => ['required'],
            // '*.maximum_leave_days' => ['required'],
        ];
    }

     public function batchSize(): int
    {
        return 150;
    }

    public function chunkSize(): int
    {
        return 150;
    }
}
