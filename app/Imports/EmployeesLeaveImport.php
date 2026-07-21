<?php

namespace App\Imports;

use App\Models\Employee;
use App\Models\EmployeeLeave;
use App\Models\LeaveType;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
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

    protected array $skipped = [];

    protected int $rowNumber = 1; // heading row is row 1, so first data row is row 2

     public function limit(): int
    {
        return 2500; // Import only the first 100 rows
    }
    public function collection(Collection $rows)
    {
       foreach($rows as $row){

        $this->rowNumber++;

        if($row->filter()->isNotEmpty()){

        // One-off check on the very first data row: confirm the headings we
        // rely on actually exist in this file, so a renamed/misspelled
        // column header shows up as a clear log entry instead of silent nulls.
        if ($this->rowNumber === 2) {
            
            $expectedHeadings = ['employee_number', 'fullname', 'leave_type', 'accrual_rate', 'available_leave_days', 'maximum_leave_days'];
            $missingHeadings = array_filter($expectedHeadings, fn ($heading) => !$row->has($heading));
            if (!empty($missingHeadings)) {
                Log::warning('EmployeesLeaveImport: expected column heading(s) not found in file', [
                    'missing_headings' => array_values($missingHeadings),
                    'headings_found' => $row->keys()->all(),
                ]);
            }
        }

        $employeeNumber = trim($row['employee_number'] ?? '');
        $fullname = strtolower(trim($row['fullname'] ?? ''));

        if (!$employeeNumber && !$fullname) {
            $this->skipped[] = ['row' => $this->rowNumber, 'reason' => 'missing_employee_number_and_fullname'];
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
            $this->skipped[] = [
                'row' => $this->rowNumber,
                'reason' => 'employee_not_found',
                'employee_number' => $employeeNumber,
                'fullname' => $fullname,
            ];
            continue;
        }

        $leaveType = LeaveType::whereRaw('LOWER(TRIM(name)) = ?', [strtolower(trim($row['leave_type'] ?? ''))])->first();


        if (!$leaveType) {
            $this->skipped[] = [
                'row' => $this->rowNumber,
                'reason' => 'leave_type_not_found',
                'employee_number' => $employeeNumber,
                'leave_type' => $row['leave_type'] ?? null,
            ];
            continue;
        }

        // Diagnostic: log whenever maximum_leave_days comes through empty/non-numeric
        // so blank cells or bad formulas in the source file are traceable per row.
        $maximumLeaveDaysRaw = $row['maximum_leave_days'] ?? null;
        if ($maximumLeaveDaysRaw === null || $maximumLeaveDaysRaw === '' || !is_numeric($maximumLeaveDaysRaw)) {
            Log::warning('EmployeesLeaveImport: maximum_leave_days is blank or non-numeric', [
                'row' => $this->rowNumber,
                'employee_number' => $employeeNumber,
                'leave_type' => $leaveType->name,
                'maximum_leave_days_raw' => $maximumLeaveDaysRaw,
            ]);
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

    public function getSkippedRows(): array
    {
        return $this->skipped;
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
