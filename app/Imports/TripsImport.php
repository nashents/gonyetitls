<?php

namespace App\Imports;

use Carbon\Carbon;
use App\Models\Trip;
use App\Models\Cargo;
use App\Models\Horse;
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

class TripsImport implements ToCollection, SkipsEmptyRows, WithLimit, 
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

    public function __construct()
    {
        $this->company = Auth::user()->employee->company;
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
                $trailer_ids = [];

                $trip_ref = trim($row->get('trip_reference'));
                $start_date = $this->parseExcelDate($row->get('start_date'));
                $end_date = $this->parseExcelDate($row->get('end_date'));

                $driver_name = trim($row->get('driver'));
                $driver = null;

                if ($driver_name) {
                    $name_parts = explode(' ', $driver_name);
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
                }

                $horse = Horse::where('registration_number', 'LIKE', '%' . trim($row->get('horse_registration_number')) . '%')->first();
                $vehicle = $horse?->vehicle;

                $customer = Customer::where('name', 'LIKE', '%' . trim($row->get('customer')) . '%')->first();
                $loading_point = LoadingPoint::where('name', 'LIKE', '%' . trim($row->get('loading_point')) . '%')->first();
                $offloading_point = OffloadingPoint::where('name', 'LIKE', '%' . trim($row->get('offloading_point')) . '%')->first();
                $cargo = Cargo::where('name', 'LIKE', '%' . trim($row->get('cargo')) . '%')->first();
                $transporter = Transporter::where('name', 'LIKE', '%' . trim($row->get('transporter')) . '%')->first();
                $trip_type = TripType::where('name', 'LIKE', '%' . trim($row->get('trip_type')) . '%')->first();
                $currency = Currency::where('name', 'LIKE', '%' . trim($row->get('currency')) . '%')->first();

                // Trailer IDs
                $trailer_ids = [];
                $trailer_regs = explode(',', trim($row->get('trailer_reg_numbers') ?? ''));
                foreach ($trailer_regs as $reg) {
                    $trailer = Trailer::where('registration_number', 'LIKE', '%' . trim($reg) . '%')->first();
                    if ($trailer) {
                        $trailer_ids[] = $trailer->id;
                    }
                }

                $trip = new Trip();
                $trip->trip_number = $trip_number;
                $trip->user_id = $user_id;
                $trip->company_id = $company_id;
                $trip->trip_ref = $trip_ref;
                $trip->trip_type_id = $trip_type?->id;
                $trip->transporter_id = $transporter?->id;
                $trip->horse_id = $horse?->id;
                $trip->driver_id = $driver?->id;
                $trip->vehicle_id = $vehicle?->id;
                $trip->customer_id = $customer?->id;
                $trip->currency_id = $currency?->id;
                $trip->loading_point_id = $loading_point?->id;
                $trip->offloading_point_id = $offloading_point?->id;
                $trip->cargo_id = $cargo?->id;
                $trip->start_date = $start_date;
                $trip->end_date = $end_date;
                $trip->trip_status = trim($row->get('trip_status'));
                $trip->rate = $row->get('rate');
                $trip->freight = $row->get('freight');
                $trip->weight = $row->get('weight');
                $trip->litreage = $row->get('litreage_at_ambient');
                $trip->litreage_at_20 = $row->get('litreage_at_20');

                $trip->with_trailer = count($trailer_ids) > 0 ? 1 : 0;
                $trip->with_customer_rates = "custom";
                $trip->with_transporter_rates = "custom";
                $trip->freight_calculation = $row->get('rate') ? 'flat_rate' : null;

                if ($cargo?->type === "Solid") {
                    $trip->calculation_measurement = "weight";
                } elseif ($cargo?->type === "Liquid") {
                    $trip->calculation_measurement = "litreage_at_20";
                }

                $trip->turnover = $trip->freight;
                $trip->cost_of_sales = 0;
                $trip->net_profit = $trip->turnover;
                $trip->net_profit_percentage = 100;
                $trip->markup_percentage = 100;

                $trip->save();

                if (!empty($trailer_ids)) {
                    $trip->trailers()->sync($trailer_ids);
                }

                // Save Delivery Note
                $delivery_note = new DeliveryNote();
                $delivery_note->user_id = $user_id;
                $delivery_note->trip_id = $trip->id;
                $delivery_note->loaded_date = $start_date;
                $delivery_note->offloaded_date = $end_date;
                $delivery_note->loaded_litreage = $trip->litreage;
                $delivery_note->loaded_litreage_at_20 = $trip->litreage_at_20;
                $delivery_note->loaded_weight = $trip->weight;
                $delivery_note->loaded_rate = $trip->rate;
                $delivery_note->loaded_freight = $trip->freight;
                $delivery_note->offloaded_weight = $row->get('offloaded_weight');
                $delivery_note->offloaded_litreage = $row->get('offloaded_litreage_at_ambient');
                $delivery_note->offloaded_litreage_at_20 = $row->get('offloaded_litreage_at_20');
                $delivery_note->save();

                // Update statuses if trip is completed or cancelled
                if (in_array($trip->trip_status, ['Offloaded', 'Cancelled', 'Scheduled'])) {
                    if ($horse) $horse->update(['status' => 1]);
                    if ($vehicle) $vehicle->update(['status' => 1]);
                    if ($driver) $driver->update(['status' => 1]);

                    foreach ($trip->trailers as $trailer) {
                        $trailer?->update(['status' => 1]);
                    }

                    foreach ($trip->breakdown_assignments as $ba) {
                        Horse::withTrashed()->find($ba->horse_id)?->update(['status' => 1]);
                        Vehicle::withTrashed()->find($ba->vehicle_id)?->update(['status' => 1]);
                        Driver::withTrashed()->find($ba->driver_id)?->update(['status' => 1]);

                        foreach ($ba->trailers as $t) {
                            Trailer::withTrashed()->find($t->id)?->update(['status' => 1]);
                        }
                    }
                }

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
        return 150;
    }

    public function chunkSize(): int
    {
        return 150;
    }
}
