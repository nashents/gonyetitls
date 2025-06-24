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

    public $company;
    public $trailer_ids;

    public function __construct()
    {
        $this->company = Auth::user()->employee->company;
    }

       private function parseExcelDate($value)
       {
           if (!isset($value)) {
               return null;
           }
   
           // If it's a numeric Excel date serial
           if (is_numeric($value)) {
               try {
                   return Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value)->format('Y-m-d'));
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
        return 500; // Import only the first 100 rows
    }

    public function collection(Collection $rows)
    {


       foreach($rows as $row){
        if($row->filter()->isNotEmpty()){

            $offloading_point = OffloadingPoint::where('name',$row['offloading_point'])->first();
            $customer = Customer::where('name',$row['customer'])->first();
            $horse = Horse::where('fleet_number',$row['fleet_number'])->first();
            $cargo = Cargo::where('name',$row['cargo'])->first();
            $transporter = Transporter::where('name',$row['transporter'])->first();            
            $employee = Employee::where('surname', $row['driver'])->first();
            if($employee){
                $driver = $employee->driver;
            }else{
                $driver = Null;
            }
            

            $trip = new Shift;
            $trip->user_id     = Auth::user()->id;
            $trip->trip_type_id = $trip_type ? $trip_type->id : Null;
            $trip->trip_ref = $row['trip_reference'];
            $trip->start_date = $this->parseExcelDate($row['start_date']);
            $trip->end_date = $this->parseExcelDate($row['end_date']);
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
