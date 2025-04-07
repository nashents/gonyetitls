<?php

namespace App\Imports;

use App\Models\Employee;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class EmployeesLeaveImport implements  ToCollection,
WithHeadingRow,
SkipsOnError,
WithValidation,
WithChunkReading
{
    use Importable, SkipsErrors;
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function collection(Collection $rows)
    {
       foreach($rows as $row){
        if($row->filter()->isNotEmpty()){
     
        $employee = Employee::where('employee_number',$row['employee_number'])->first();


        if (isset($employee)) {
          
            $employee->accrual_rate    = $row['accrual_rate'];
            $employee->leave_days    = $row['available_leave_days'];
            $employee->maximum_leave_days    = $row['maximum_leave_days'];
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

    public function chunkSize(): int
    {
        return 10;
    }
}
