<?php

namespace App\Imports;

use Carbon\Carbon;
use App\Models\Shift;
use App\Models\Cargo;
use App\Models\Horse;
use App\Models\Driver;
use App\Models\Trailer;
use App\Models\Vehicle;
use App\Models\Currency;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\ShiftType;
use App\Models\Transporter;
use App\Models\DeliveryNote;
use App\Models\LoadingPoint;
use App\Models\OffloadingPoint;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
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

class ShiftsImport implements ToCollection, SkipsEmptyRows, WithLimit, 
WithHeadingRow,
SkipsOnError,
WithValidation,
WithChunkReading,
WithBatchInserts
{
    use Importable, SkipsErrors;

    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */

    public $for;

    public function __construct($for)
    {   $this->for = $for;
       
    }

        private function parseExcelDate($value)
        {
            if (!isset($value)) {
                return null;
            }

            // If it's a numeric Excel date serial
            if (is_numeric($value)) {
                try {
                    return Carbon::instance(
                        \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value)
                    );
                } catch (\Exception $e) {
                    return null;
                }
            }

            // If it's a string in strict YYYY-MM-DD format
            if (is_string($value)) {
                try {
                    $parsed = Carbon::createFromFormat('Y-m-d', $value);
                    return $parsed && $parsed->format('Y-m-d') === $value ? $parsed : null;
                } catch (\Exception $e) {
                    return null;
                }
            }

            return null;
        }

       public function limit(): int
    {
        return 2500; // Import only the first 100 rows
    }

    public function collection(Collection $rows)
    {


       foreach($rows as $row){
        if($row->filter()->isNotEmpty()){

            $customer = Customer::where('name',$row['customer'])->first();
            $horse = Horse::where('fleet_number',$row['horse'])->first();
            $cargo = Cargo::where('name',$row['cargo'])->first();      
            $employee = Employee::where('surname', $row['driver'])->first();
            if($employee){
                $driver = $employee->driver;
            }else{
                $driver = Null;
            }
            

            $shift = new Shift;
            $shift->user_id     = Auth::user()->id;
            if ($row['shift'] == "Morning") {
                $shift->type = "Day";
            }elseif($row['shift'] == "Night"){
                $shift->type = "Night";
            }

            $parsedDate = $this->parseExcelDate($row['date']);
            $shift->date = $parsedDate ? $parsedDate->format('Y-m-d') : null;
            $shift->shift_start_time     = $row['shift_start'];
            $shift->shift_end_time     = $row['shift_close'];
            $shift->horse_id     = $horse ? $horse->id : Null;
            $shift->driver_id     = $driver ? $driver->id : Null;
            $shift->customer_id     = $customer ? $customer->id : Null;
            $shift->cargo_id     = $cargo ? $cargo->id : Null;
            $shift->actual_mileage     = $row['actual_mileage'];
            $shift->calculated_mileage     = $row['cal_mileage'];
            $shift->open_mileage     = $row['open_mileage'];
            $shift->close_mileage     = $row['close_mileage'];
            $shift->fuel_consumption_mileage     = $row['consumption'];
            $shift->save();

            
            
    }
       }
    }

    public function rules(): array{
        return[
            // '*.transporter_id' => ['required'],
        ];
    }


    public function batchSize(): int
    {
        return 10;
    }

    public function chunkSize(): int
    {
        return 10;
    }
}
