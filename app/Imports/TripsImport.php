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

       public function limit(): int
    {
        return 500; // Import only the first 100 rows
    }

    public function collection(Collection $rows)
    {


       foreach($rows as $row){
        if($row->filter()->isNotEmpty()){

            $loading_point = LoadingPoint::where('name','LIKE','%'.$row['loading_point'].'%')->first();
            $customer = Customer::where('name','LIKE','%'.$row['customer'].'%')->first();
            if (isset($row['driver']) && $row['driver'] != "") {
                $driver_name_array = explode(' ', $row['driver']);
                if (isset($driver_name_array)) {
                    if (isset($driver_name_array[0]) && isset($driver_name_array[2])) {
                        $employee = Employee::where('name','LIKE','%'.$driver_name_array[0].'%')
                            ->where('surname','LIKE','%'.$driver_name_array[2].'%')->first();
                    }elseif(isset($driver_name_array[0]) && isset($driver_name_array[1])){
                        $employee = Employee::where('name','LIKE','%'.$driver_name_array[0].'%')
                        ->where('surname','LIKE','%'.$driver_name_array[1].'%')->first();
                    }
                }
            }
            
                   
            $horse = Horse::where('registration_number','LIKE','%'.$row['horse_registration_number'].'%')->first();
            $cargo = Cargo::where('name','LIKE','%'.$row['cargo'].'%')->first();
            $transporter = Transporter::where('name','LIKE','%'.$row['transporter'].'%')->first();
            $trip_type = TripType::where('name','LIKE','%'.$row['trip_type'].'%')->first();
            $currency = Currency::where('name','LIKE','%'.$row['currency'].'%')->first();
            $offloading_point = OffloadingPoint::where('name','LIKE','%'. $row['offloading_point'] ?? null.'%')->first();
            
            if (isset($row['trailer_reg_numbers'])) {
                $regnumbers = explode(',', $row['trailer_reg_numbers']);

                if (isset($regnumbers)) {
                    foreach ($regnumbers as $regnumber) {
                        $trailer = Trailer::where('registration_number','LIKE','%'.$regnumber.'%')->first();
                        if (isset($trailer)) {
                            $trailer_ids[] = $trailer->id;
                        }
                    }
                }
            }
            
            if(isset($employee)){
                $driver = $employee->driver;
            }else{
                $driver = Null;
            }
            

            $trip = new Trip;
            $trip->trip_number = $this->tripNumber() ;
            $trip->user_id     = Auth::user()->id;
            $trip->company_id = $this->company->id;
            $trip->trip_type_id = $trip_type ? $trip_type->id : Null;
            $trip->trip_ref = $row['trip_reference'];
            $trip->start_date = isset($row['start_date']) ? Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['start_date'])) : null;
            $trip->end_date = isset($row['offloading_date']) ? Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['offloading_date'])) : null;
            $trip->transporter_id     = $transporter ? $transporter->id : Null;
            $trip->horse_id     = $horse ? $horse->id : Null;
            if (isset($this->trailer_ids)) {
                $trip->with_trailer = 1;
            }
            $trip->driver_id     = $driver ? $driver->id : Null;
            $trip->customer_id     = $customer ? $customer->id : Null;
            $trip->currency_id     = $currency ? $currency->id : Null;
            $trip->loading_point_id     = $loading_point ? $loading_point->id : Null;
            $trip->offloading_point_id     = $offloading_point ? $offloading_point->id : Null;
            $trip->cargo_id     = $cargo ? $cargo->id : Null;
            if(isset($cargo)){
                $cargo_type = $cargo->type;
                if ($cargo_type && $cargo_type == "Solid") {
                    $trip->calculation_measurement = "weight";
                }elseif ($cargo_type && $cargo_type == "Liquid") {
                    $trip->calculation_measurement = "litreage_at_20";
                }
                $trip->with_cargos = 1;
            }
            $trip->with_customer_rates = "custom";
            $trip->with_transporter_rates = "custom";
            $trip->weight     = $row['weight'];
            $trip->litreage     = $row['litreage_at_ambient'];
            $trip->litreage_at_20     = $row['litreage_at_20'];
            $trip->rate     = $row['rate'];
            if (isset($row['rate'])) {
               $trip->freight_calculation = 'flat_rate';
               $trip->with_customer_rates = 'custom';
            }
            $trip->freight     = $row['freight'];
            $trip->turnover = $row['freight'];
            $turnover = $row['freight'];
            $cost_of_sales = 0;
            $trip->cost_of_sales = 0;
            $trip->trip_status     = $row['trip_status'];
            $trip->save();

            if (isset($this->trailer_ids) && !empty($this->trailer_ids) && !is_null($this->trailer_ids) ) {
                $trip->trailers()->sync($this->trailer_ids);
              }

              if ((isset($cost_of_sales) && is_numeric($cost_of_sales) && $cost_of_sales > 0) && (isset($turnover) && is_numeric($turnover) && $turnover > 0)) {
         
                $trip->net_profit = $turnover - $cost_of_sales;
                $this->net_profit = $turnover - $cost_of_sales;
    
                if((is_numeric($this->net_profit) && $this->net_profit > 0) && (is_numeric($turnover) && $turnover > 0)){
                    $trip->markup_percentage = (($this->net_profit/$cost_of_sales) * 100);
                    $trip->net_profit_percentage = (($this->net_profit/$turnover) * 100);
                }
          
            }else {

                $trip->net_profit_percentage = 100 ;
                $trip->markup_percentage = 100 ;
            }



                $delivery_note = new DeliveryNote;
                $delivery_note->user_id =  $trip->user_id;
                $delivery_note->trip_id = $trip->id;
                $delivery_note->loaded_date =  $trip->start_date;
                $delivery_note->loaded_litreage =  $trip->litreage;
                $delivery_note->loaded_litreage_at_20 =  $trip->litreage_at_20;
                $delivery_note->loaded_weight = $trip->weight;
                $delivery_note->loaded_rate = $trip->rate;
                $delivery_note->loaded_freight = $trip->freight;
                $delivery_note->offloaded_date =   $trip->end_date;
                $delivery_note->offloaded_weight = $row['offloaded_weight'];
                $delivery_note->offloaded_litreage = $row['offloaded_litreage_at_ambient'];
                $delivery_note->offloaded_litreage_at_20 = $row['offloaded_litreage_at_20'];
                $delivery_note->save();


                
          if ($row['trip_status'] == "Offloaded" || $row['trip_status'] == "Cancelled" || $row['trip_status'] == "Scheduled") {
            
            $horse = Horse::withTrashed()->find($trip->horse_id);
            if (isset($horse)) {
                $horse->status = 1;
                $horse->update();
            }
            $vehicle = Vehicle::withTrashed()->find($trip->vehicle_id);
            if (isset($vehicle)) {
                $vehicle->status = 1;
                $vehicle->update();
            }
          
            $driver = Driver::withTrashed()->find($trip->driver_id);
            if (isset($driver)) {
                $driver->status = 1;
                $driver->update();
            }
           

            if ($trip->trailers->count()>0) {
                foreach ($trip->trailers as $trailer) {
                    $trailer = Trailer::withTrashed()->find($trailer->id);
                    $trailer->status = 1;
                    $trailer->update();
                }
            }
            
            $breakdown_assignments = $trip->breakdown_assignments;
            if ($breakdown_assignments->count()>0) {
            
            foreach ($breakdown_assignments as $breakdown_assignment) {

                $horse = Horse::withTrashed()->find($breakdown_assignment->horse_id);
                $horse->status = 1;
                $horse->update();

                $vehicle = Vehicle::withTrashed()->find($breakdown_assignment->vehicle_id);
                $vehicle->status = 1;
                $vehicle->update();
    
                $driver = Driver::withTrashed()->find($breakdown_assignment->driver_id);
                $driver->status = 1;
                $driver->update();

    
                if ($breakdown_assignment->trailers->count()>0) {
                    foreach ($trip->trailers as $trailer) {
                        $trailer = Trailer::withTrashed()->find($trailer->id);
                        $trailer->status = 1;
                        $trailer->update();
                    }
                }
            }
                # code...
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
        return 10;
    }

    public function chunkSize(): int
    {
        return 10;
    }
}
