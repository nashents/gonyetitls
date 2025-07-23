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
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;

class ShiftTripsImport implements ToCollection, SkipsEmptyRows, WithLimit, 
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

    protected $company;
    protected $initialShiftId;
    

    public function __construct()
    {
        $this->initialShiftId = Shift::max('id') ?? 0;
        $this->company = Auth::user()->employee->company;
    
    }

    private function generateNumber($prefix, $id)
    {
        $initials = collect(explode(' ', $this->company->name))->map(fn($word) => $word[0])->implode('');
        return $initials . $prefix . str_pad($id + 1, 5, '0', STR_PAD_LEFT);
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

    private function calculateFuelConsumption($actual_mileage, $fuel)
    {
        if (is_numeric($actual_mileage) && $actual_mileage > 0 && is_numeric($fuel) && $fuel > 0) {
            return round($actual_mileage / $fuel, 2); // km per litre
        }
        return null;
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
          
             if ($row->filter()->isNotEmpty()) {

                 
                $trip_number = $this->tripNumber();
                $user_id = Auth::id();
                $company_id = $this->company->id;

                $date = $this->parseExcelDate($row->get('date'))?->format('Y-m-d') ?? Carbon::today()->format('Y-m-d');

                // Vehicle & Entities
                $horse = Horse::where('fleet_number', $row->get('fleet_number'))->first();
                $transporter = $horse?->transporter;

                $customer = Customer::where('name', $row->get('customer'))->first();
                $loading_point = LoadingPoint::where('name', $row->get('loading_point'))->first();
                $offloading_point = OffloadingPoint::where('name', $row->get('offloading_point'))->first();
                $cargo = Cargo::where('name', $row->get('cargo'))->first();

                // Driver resolution
                $employee = null;
                $driver = null;
                $driver_name = trim($row->get('driver'));
                if ($driver_name) {
                    $name_parts = preg_split('/\s+/', $driver_name);

                    if ($name_parts && count($name_parts) >= 2) {
                        $name = $name_parts[0] ?? null;
                        $surname = $name_parts[1] ?? null;

                        if ($name && $surname) {
                            $employee = Employee::where('name', 'LIKE', "%$name%")
                                                ->where('surname', 'LIKE', "%$surname%")
                                                ->first();
                        } elseif ($surname) {
                            $employee = Employee::where('surname', 'LIKE', "%$surname%")->first();
                        }

                    }elseif ($name_parts && count($name_parts) === 1) {
                        $surname = $name_parts[0] ?? null;
                       
                       if ($surname) {
                            $employee = Employee::where('surname', 'LIKE', "%$surname%")->first();
                        }
                    }
                  
                    $driver = $employee?->driver;

                    
                }

                // Mileage
                $open_mileage = $row->get('starting_mileage');
                $close_mileage = $row->get('ending_mileage');
                $actual_mileage = null;

                if (is_numeric($open_mileage) && is_numeric($close_mileage) && $close_mileage >= $open_mileage) {
                    $actual_mileage = $close_mileage - $open_mileage;
                }

                $fuel = $row->get('fuel');
             
                // Shift Creation
                $shift = Shift::firstOrCreate(
                    [
                        'type' => $row->get('shift'),
                        'date' => $date,
                        'driver_id' => $driver?->id,
                    ],
                    [
                        'user_id' => $user_id,
                        'shift_number' => $this->generateNumber('S', ++$this->initialShiftId),
                        'shift_start_time' => $this->parseExcelTime($row->get('shift_start')),
                        'shift_end_time' => $this->parseExcelTime($row->get('shift_end')),
                        'horse_id' => $horse?->id,
                        'customer_id' => $customer?->id,
                        'transporter_id' => $transporter?->id,
                        'cargo_id' => $cargo?->id,
                        'calculated_mileage' => $row->get('calculated_mileage'),
                        'open_mileage' => $open_mileage,
                        'close_mileage' => $close_mileage,
                        'actual_mileage' => $actual_mileage,
                        'fuel_consumption_mileage' => $this->calculateFuelConsumption($actual_mileage, $fuel),
                        'equipment' => 'Horse',
                        'total_fuel' => $fuel,
                        'currency_id' => $this->company->currency_id,
                        'company_id' => $this->company->id,
                        'authorization' => 'approved',
                        'authorized_by_id' => $user_id,
                        'authorization_date' => $date,
                        'status' => false,
                        'for' => 'Trips',
                    ]
                );
                if ($loading_point) {
                     $shift->loading_points()->syncWithoutDetaching($loading_point->id);
                }
                if ($offloading_point) {
                     $shift->offloading_points()->syncWithoutDetaching($offloading_point->id);
                }
             
               $trip_type = TripType::where('name','Local')->first();

                // Trip Timing
                $arrive_loading_point = $this->parseExcelTime($row->get('arrive_loading_point'));
                $depart_loading_point = $this->parseExcelTime($row->get('depart_loading_point'));
                $arrive_offloading_point = $this->parseExcelTime($row->get('arrive_offloading_point'));
                $depart_offloading_point = $this->parseExcelTime($row->get('depart_offloading_point'));

                $weight = $row->get('weight');
                $rate = $this->company->default_rate;
                $freight = 0;

                if (($weight && is_numeric($weight) && $weight>0) && ($rate && is_numeric($rate) && $rate > 0)) {
                    $freight = $weight * $rate;
                }
                // Trip Creation
                $trip = new Trip();
                $trip->trip_number = $trip_number;
                $trip->user_id = $user_id;
                $trip->trip_type_id = $trip_type?->id;
                $trip->shift_id = $shift->id;
                $trip->company_id = $company_id;
                $trip->transporter_id = $transporter?->id;
                $trip->start_date = $date;
                $trip->end_date = $date;
                $trip->customer_id = $customer?->id;
                $trip->cargo_id = $cargo?->id;
                $trip->weight = $weight;
                $trip->rate = $rate;
                $trip->currency_id = $this->company->currency_id;
                $trip->freight = $freight;
                $trip->driver_id = $driver?->id;
                $trip->horse_id = $horse?->id;
                $trip->trip_status = 'Offloaded';
                $trip->trip_status_date = $date;
                $trip->loading_point_id = $loading_point?->id;
                $trip->arrive_loading_point = $arrive_loading_point;
                $trip->depart_loading_point = $depart_loading_point;
                $trip->loading_time = $this->calculateTimeDifference($arrive_loading_point, $depart_loading_point);
                $trip->offloading_point_id = $offloading_point?->id;
                $trip->arrive_offloading_point = $arrive_offloading_point;
                $trip->depart_offloading_point = $depart_offloading_point;
                $trip->offloading_time = $this->calculateTimeDifference($arrive_offloading_point, $depart_offloading_point);
                $trip->authorized_by_id = $user_id;
                $trip->authorization = 'approved';
                $trip->authorization_date = $date;
                $trip->save();

                $delivery_note = new DeliveryNote();
                $delivery_note->user_id = $user_id;
                $delivery_note->trip_id = $trip->id;
                $delivery_note->loaded_date = $date;
                $delivery_note->offloaded_date = $date;
                $delivery_note->loaded_litreage = $trip->litreage;
                $delivery_note->loaded_litreage_at_20 = $trip->litreage_at_20;
                $delivery_note->loaded_weight = $trip->weight;
                $delivery_note->loaded_rate = $trip->rate;
                $delivery_note->loaded_freight = $trip->freight;
                $delivery_note->offloaded_weight = $trip->weight;
                $delivery_note->offloaded_litreage = $trip->litreage;
                $delivery_note->offloaded_litreage_at_20 = $trip->litreage_at_20;
                $delivery_note->save();

                $shift->load('trips');
                $shift->total_loads = $shift->trips->count();
                $shift->total_freight = $shift->trips->sum(function ($trip) {
                    return is_numeric($trip->freight) ? $trip->freight : 0;
                });

                $shift->total_weight = $shift->trips->sum(function ($trip) {
                    return is_numeric($trip->weight) ? $trip->weight : 0;
                });
                $shift->save();

            }

        }
    }

    // Helper method to calculate time difference in HH:MM
    private function calculateTimeDifference($start, $end)
    {
        if ($start && $end) {
            $start = $start instanceof Carbon ? $start : Carbon::parse($start);
            $end = $end instanceof Carbon ? $end : Carbon::parse($end);
            $diff = $end->diffInMinutes($start);
            return sprintf('%02d:%02d', floor($diff / 60), $diff % 60);
        }
        return null;
    }


    public function rules(): array{
        return[
            // '*.transporter_id' => ['required'],
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
