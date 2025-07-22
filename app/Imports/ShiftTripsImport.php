<?php

namespace App\Imports;

use Carbon\Carbon;
use App\Models\Trip;
use App\Models\Cargo;
use App\Models\Horse;
use App\Models\Shift;
use App\Models\Driver;
use App\Models\Trailer;
use App\Models\Vehicle;
use App\Models\Currency;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\TripType;
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

class ShiftTripsImport implements ToCollection, SkipsEmptyRows, WithLimit, 
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

    public $company;
    public $trailer_ids;
    public $transporter;

    public function __construct()
    {
        $this->company = Auth::user()->employee->company;
        $this->transporter = $this->company->transporters->first();
    }

    public function tripNumber(){

        if ($this->company) {
               $str = $this->company->name;
               $words = explode(' ', $str);
               if (isset($words[1][0])) {
                   $initials = $words[0][0].$words[1][0];
               }else {
                   $initials = $words[0][0];
               }
           }
    
           $trip = Trip::orderBy('id','desc')->first();
   
           if (!$trip) {
               $trip_number =  $initials .'T'. str_pad(1, 5, "0", STR_PAD_LEFT);
           }else {
               $number = $trip->id + 1;
               $trip_number =  $initials .'T'. str_pad($number, 5, "0", STR_PAD_LEFT);
           }
   
           return  $trip_number;
   
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

        private function parseExcelTime($value)
        {
            if (empty($value)) {
                return null;
            }

            try {
                if (is_numeric($value)) {
                    // Excel stores time as fraction of a day (e.g. 0.5 = 12:00 PM)
                    $time = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value);
                    return $time->format('H:i:s');
                }

                // If it is already a string like '1:10:00 AM'
                $parsed = Carbon::createFromFormat('g:i:s A', $value);
                return $parsed->format('H:i:s');

            } catch (\Exception $e) {
                return null; // fallback
            }
        }

    public function limit(): int
    {
        return 2500; // Import only the first 100 rows
    }

    public function collection(Collection $rows)
    {


       foreach($rows as $row){
            if($row->filter()->isNotEmpty()){
                
                $trip_number = $this->tripNumber();
                $user_id = Auth::id();
                $company_id = $this->company->id;

                $date = $this->parseExcelDate($row->get('date'))?->format('Y-m-d');
            
                $horse = Horse::where('fleet_number', 'LIKE', '%' . trim($row->get('fleet_number')) . '%')->first();
                $customer = Customer::where('name', 'LIKE', '%' . trim($row->get('customer')) . '%')->first();
                $loading_point = LoadingPoint::where('name', 'LIKE', '%' . trim($row->get('loading_point')) . '%')->first();
                $offloading_point = OffloadingPoint::where('name', 'LIKE', '%' . trim($row->get('offloading_point')) . '%')->first();
                $cargo = Cargo::where('name', 'LIKE', '%' . trim($row->get('cargo')) . '%')->first();
                $transporter = Transporter::where('name', 'LIKE', '%' . trim($row->get('transporter')) . '%')->first();
               
                $driver_name = trim($row->get('driver'));
                $driver = null;

                 $driver_name = trim($row->get('driver')); 
                $driver = null;
              

                if ($driver_name) {
                    // Use regex to split and clean up whitespace
                    $name_parts = preg_split('/\s+/', $driver_name, -1, PREG_SPLIT_NO_EMPTY);
                  
                    if (count($name_parts) >= 2) {
                        $name = $name_parts[0];
                        $surname = $name_parts[1] ?? $name_parts[2] ?? null;
                        if ($surname) {
                            $employee = Employee::where('name', 'LIKE', "%$name%")
                                ->where('surname', 'LIKE', "%$surname%")
                                ->first();
                            $driver = $employee?->driver;
                        }
                    }

                    if (count($name_parts) === 1) {
                        $surname = $name_parts[0];
                        if ($surname) {
                            $employee = Employee::where('surname', 'LIKE', "%$surname%")->first();
                            $driver = $employee?->driver;    
                        }
                    }
                }


                $shift = Shift::firstOrNew([
                    'type' => $row->get('shift'),
                    'date' => $date,
                    'driver_id' => $driver?->id,
                ]);

                // Update or set remaining fields
                $shift->user_id = Auth::id();
                $shift->shift_start_time =  $this->parseExcelTime($row->get('shift_start'));
                $shift->shift_end_time =  $this->parseExcelTime($row->get('shift_end'));
                $shift->for = "Trips";
                $shift->horse_id = $horse?->id;
                $shift->cargo_id = $cargo?->id;
                $shift->customer_id = $customer?->id;
                $shift->authorization = "approved";
                $shift->authorized_by_id = Auth::id();
                $shift->authorization_date = Carbon::today()->format('Y-m-d');
                $shift->status = False;
                $shift->save();

                $trip = new Trip();
                $trip->trip_number = $trip_number;
                $trip->user_id = $user_id;
                $trip->shift_id = $shift?->id;
                $trip->company_id = $company_id;
                $trip->transporter_id = $transporter?->id;
                $trip->start_date = $date;
                $trip->end_date = $date;
                $trip->customer_id = $customer?->id;
                $trip->cargo_id = $cargo?->id;
                $trip->weight = $row->get('weight');
                $trip->driver_id = $driver?->id;
                $trip->horse_id = $horse?->id;
                $trip->trip_status = "Offloaded";
                $trip->trip_status_date = $date;
                $trip->loading_point_id = $loading_point?->id;
                $trip->arrive_loading_point =  $this->parseExcelTime($row->get('arrive_loading_point'));
                $trip->loading_time = trim($row->get('loading_time'));
                $trip->depart_loading_point =  $this->parseExcelTime($row->get('depart_loading_point'));
                $trip->offloading_point_id = $offloading_point?->id;
                $trip->arrive_offloading_point=  $this->parseExcelTime($row->get('arrive_offloading_point'));
                $trip->offloading_time = trim($row->get('offloading_time'));
                $trip->depart_offloading_point =  $this->parseExcelTime($row->get('depart_offloading_point'));
                $trip->starting_mileage = $row->get('starting_mileage');
                $trip->ending_mileage = $row->get('ending_mileage');
                $trip->actual_mileage = $row->get('actual_mileage');
                $trip->calculated_mileage = $row->get('calculated_mileage');
                $trip->trip_fuel = $row->get('fuel');
                $trip->fuel_consumption = $row->get('fuel_consumption');
                $trip->authorized_by_id = Auth::id();
                $trip->authorization = "approved";
                $trip->authorization_date = Carbon::today()->format('Y-m-d');
                $trip->save();

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
        return 50;
    }

    public function chunkSize(): int
    {
        return 50;
    }
}
