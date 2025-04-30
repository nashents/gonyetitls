<?php

namespace App\Imports;

use App\Models\Vehicle;
use App\Models\Transporter;
use App\Models\VehicleMake;
use App\Models\VehicleModel;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class VehiclesImport implements ToCollection,
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

    public function vehicleNumber(){
       
        if (isset(Auth::user()->company)) {
            $str = Auth::user()->company->name;
            $words = explode(' ', $str);
            if (isset($words[1][0])) {
                $initials = $words[0][0].$words[1][0];
            }else {
                $initials = $words[0][0];
            }
        }elseif (isset(Auth::user()->employee->company)) {
            $str = Auth::user()->employee->company->name;
            $words = explode(' ', $str);
            if (isset($words[1][0])) {
                $initials = $words[0][0].$words[1][0];
            }else {
                $initials = $words[0][0];
            }
        }

            $vehicle = Vehicle::orderBy('id', 'desc')->first();

        if (!$vehicle) {
            $vehicle_number =  $initials .'V'. str_pad(1, 5, "0", STR_PAD_LEFT);
        }else {
            $number = $vehicle->id + 1;
            $vehicle_number =  $initials .'V'. str_pad($number, 5, "0", STR_PAD_LEFT);
        }

        return  $vehicle_number;

    }
    public function collection(Collection $rows)
    {

       foreach($rows as $row){

        if($row->filter()->isNotEmpty()){
        
            $vehicle = Vehicle::where('registration_number',$row['registration_number'])->first();

        if (isset($vehicle)) {

            $transporter = Transporter::where('transporter_number', $row['transporter_number'])->first();
            if (isset($transporter)) {
                $transporter_id = $transporter->id;
            }   
            if (isset($transporter_id) && $transporter_id != "") {
                $vehicle->transporter_id     = $transporter_id;
            } 
            $make = VehicleMake::where('name', $row['make'])->first();
            if (isset($make)) {
                $make_id = $make->id;
            }    
            $model = VehicleModel::where('name', $row['model'])->first();
            if (isset($model)) {
                $model_id = $model->id;
            }   
          
            if (isset($make_id) && $make_id != "") {
                $vehicle->vehicle_make_id = $make_id;
            }else {
                $make = new VehicleMake;
                $make->name = $row['make'];
                $make->save();
                $vehicle->vehicle_make_id = $make->id;
                $make_id = $make->id;
            }
            if (isset($model_id) && $model_id != "") {
                $vehicle->vehicle_model_id = $model_id;
            }else {
                $model = new VehicleModel;
                $model->name = $row['model'];
                $model->save();
                $vehicle->vehicle_model_id = $model->id;
                $model_id = $model->id;
            }   
            
            
            $vehicle->chasis_number = $row['chasisnumber'];
            $vehicle->engine_number = $row['enginenumber'];
            $vehicle->registration_number = $row['registration_number'];
            $vehicle->fleet_number = $row['fleetnumber'];
            $vehicle->year = $row['year'];
            $vehicle->color = $row['color'];
            $vehicle->manufacturer =  $row['manufacturer'];
            $vehicle->country_of_origin =$row['country_of_origin'];
            $vehicle->mileage =$row['mileage'];
            $vehicle->fuel_type =$row['fueltype'];
            $vehicle->fuel_consumption_empty_standard    = $row['fuel_consumption_empty'] ? $row['fuel_consumption_empty'] : 0;
            $vehicle->fuel_consumption_loaded_standard    = $row['fuel_consumption_loaded'] ? $row['fuel_consumption_loaded'] : 0;
            $vehicle->update();
            $make_id = "";
            $model_id = "";
            

           
        } else {

            $transporter = Transporter::where('transporter_number', $row['transporter_number'])->first();
            if (isset($transporter)) {
                $transporter_id = $transporter->id;
            }    
            
            $make = VehicleMake::where('name', $row['make'])->first();
            if (isset($make)) {
                $make_id = $make->id;
            }  
            $model = VehicleModel::where('name', $row['model'])->first();
            if (isset($model)) {
                $model_id = $model->id;
            } 
          
            $vehicle = new Vehicle;
            if (isset($make_id) && $make_id != "") {
                $vehicle->vehicle_make_id  = $make_id;
            }else {
                $make = new VehicleMake;
                $make->name = $row['make'];
                $make->save();
                $vehicle->vehicle_make_id = $make->id;
                $make_id = $make->id;
            }  
            if (isset($model_id) && $model_id != "") {
                $vehicle->vehicle_model_id     = $model_id;
            }
            else {
                $model = new VehicleModel;
                $model->name = $row['model'];
                $model->save();
                $vehicle->vehicle_model_id = $model->id;
                $model_id = $model->id;
            }   

            if (isset($transporter_id) && $transporter_id != "") {
                $vehicle->transporter_id     = $transporter_id;
            } 

            $vehicle->user_id = Auth::user()->id;
            $vehicle->vehicle_number = $this->vehicleNumber();
            $vehicle->chasis_number = $row['chasisnumber'];
            $vehicle->engine_number = $row['enginenumber'];
            $vehicle->registration_number = $row['registration_number'];
            $vehicle->fleet_number = $row['fleetnumber'];
            $vehicle->year = $row['year'];
            $vehicle->color = $row['color'];
            $vehicle->manufacturer =  $row['manufacturer'];
            $vehicle->country_of_origin =$row['country_of_origin'];
            $vehicle->mileage = $row['mileage'];
            $vehicle->fuel_type =$row['fueltype'];
            $vehicle->fuel_consumption_empty_standard    = $row['fuel_consumption_empty'] ? $row['fuel_consumption_empty'] : 0;
            $vehicle->fuel_consumption_loaded_standard    = $row['fuel_consumption_loaded'] ? $row['fuel_consumption_loaded'] : 0;
            $vehicle->save();
            $make_id = "";
            $model_id = "";
        }
        

      
       }
    }
    }

    public function rules(): array{
        return[
            // '*.transporter_number' => ['required'],
            // '*.registration_number' => ['nullable','unique:vehicles,registration_number,NULL,id,deleted_at,NULL'],
            // '*.chasis_number' => ['nullable','unique:vehicles,chasis_number,NULL,id,deleted_at,NULL'],
            // '*.fleet_number' => ['nullable','unique:vehicles,vehicle_number,NULL,id,deleted_at,NULL'],
            // '*.engine_number' => ['nullable','unique:vehicles,engine_number,NULL,id,deleted_at,NULL']
        ];
    }




    public function chunkSize(): int
    {
        return 1000;
    }
}
