<?php

namespace App\Imports;

use App\Imports\Concerns\SkipsTemplateExampleRows;

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
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class EmployeesLeaveImport implements  ToCollection, SkipsEmptyRows, WithLimit,
WithHeadingRow,
SkipsOnError,
WithValidation,
WithChunkReading,
WithBatchInserts,
WithCalculatedFormulas,
WithMultipleSheets
{
    use SkipsTemplateExampleRows;

    use Importable, SkipsErrors;
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */

    protected array $skipped = [];

    /** employee lookup key => Employee|null */
    protected array $employeeCache = [];

    /** lower-cased name => LeaveType */
    protected $leaveTypes = null;

    protected int $rowNumber = 1; // heading row is row 1, so first data row is row 2

     public function limit(): int
    {
        return 20000; // live template lists every employee x leave type
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

        // Cached: the live template repeats each employee once per leave type.
        $employeeKey = $employeeNumber ? 'n:'.$employeeNumber : 'f:'.$fullname;
        if (!array_key_exists($employeeKey, $this->employeeCache)) {
            $this->employeeCache[$employeeKey] = Employee::query()
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
        }
        $employee = $this->employeeCache[$employeeKey];

        if (!$employee) {
            $this->skipped[] = [
                'row' => $this->rowNumber,
                'reason' => 'employee_not_found',
                'employee_number' => $employeeNumber,
                'fullname' => $fullname,
            ];
            continue;
        }

        if ($this->leaveTypes === null) {
            $this->leaveTypes = LeaveType::get()->keyBy(fn ($t) => strtolower(trim($t->name)));
        }
        $leaveType = $this->leaveTypes->get(strtolower(trim($row['leave_type'] ?? '')));


        if (!$leaveType) {
            $this->skipped[] = [
                'row' => $this->rowNumber,
                'reason' => 'leave_type_not_found',
                'employee_number' => $employeeNumber,
                'leave_type' => $row['leave_type'] ?? null,
            ];
            continue;
        }

        // Blank = keep the current figure; never clear anything.
        $data = [];
        $invalid = [];
        foreach (['accrual_rate' => 'acrual_rate', 'available_leave_days' => 'available_leave_days', 'maximum_leave_days' => 'maximum_leave_days'] as $heading => $column) {
            $value = $row[$heading] ?? null;
            if (!filled($value)) {
                continue;
            }
            if (!is_numeric($value) || $value < 0) {
                $invalid[] = $heading;
                continue;
            }
            $data[$column] = $value;
        }

        if (!empty($invalid)) {
            $this->skipped[] = [
                'row' => $this->rowNumber,
                'reason' => 'invalid_number_in_'.implode('_and_', $invalid),
                'employee_number' => $employeeNumber,
            ];
            continue;
        }

        // The live template lists every employee x leave type; a row with
        // nothing filled in must not create an empty leave record.
        if (empty($data)) {
            continue;
        }

        $employeeLeave = EmployeeLeave::firstOrNew([
            'employee_id' => $employee->id,
            'leave_type_id' => $leaveType->id,
        ]);
        foreach ($data as $column => $value) {
            // Compare as numbers so "12" vs 12.00 is not seen as a change.
            if (!is_numeric($employeeLeave->{$column}) || (float) $employeeLeave->{$column} != (float) $value) {
                $employeeLeave->{$column} = $value;
            }
        }
        // Unchanged pre-filled template rows cost a read only - no write, no audit.
        if (!$employeeLeave->isDirty()) {
            continue;
        }
        $employeeLeave->save();

        // Backward compatibility: keep employee table updated for Annual Leave
        // (filled figures only, same as the on-screen edit).
        if (strtolower(trim($leaveType->name)) === 'annual') {
            $employeeColumns = ['acrual_rate' => 'accrual_rate', 'available_leave_days' => 'leave_days', 'maximum_leave_days' => 'maximum_leave_days'];
            foreach ($data as $column => $value) {
                $attribute = $employeeColumns[$column];
                if (!is_numeric($employee->{$attribute}) || (float) $employee->{$attribute} != (float) $value) {
                    $employee->{$attribute} = $value;
                }
            }
            if ($employee->isDirty()) {
                $employee->save();
            }
        }

    }
       }
    }

    /** Only the data sheet - the live template also has Instructions + hidden Lists sheets. */
    public function sheets(): array
    {
        return [0 => $this];
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
