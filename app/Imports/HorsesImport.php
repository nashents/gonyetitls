<?php

namespace App\Imports;

use App\Models\Horse;
use App\Models\HorseMake;
use App\Models\HorseModel;
use App\Models\Transporter;
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

class HorsesImport implements ToCollection,
WithHeadingRow,
SkipsOnError,
WithValidation,
WithChunkReading
{

    use Importable, SkipsErrors;


    public $horse_number;
    public $transporter;
    public $transporter_id;
  
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */

    
    public function horseNumber(){
       
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

            $horse = Horse::orderBy('id', 'desc')->first();

        if (!$horse) {
            $horse_number =  $initials .'H'. str_pad(1, 5, "0", STR_PAD_LEFT);
        }else {
            $number = $horse->id + 1;
            $horse_number =  $initials .'H'. str_pad($number, 5, "0", STR_PAD_LEFT);
        }

        return  $horse_number;


    }
    public function collection(Collection $rows)
    {

       foreach($rows as $row){
        if($row->filter()->isNotEmpty()){

            $horse = Horse::where('registration_number',$row['registration_number'])->get()->first();

            if (isset($horse)) {
               
                $transporter = Transporter::where('transporter_number', $row['transporter_number'])->get()->first();
                if (isset($transporter)) {
                    $transporter_id = $transporter->id;
                }    
                $make = HorseMake::where('name', $row['make'])->get()->first();
                if (isset($make)) {
                    $make_id = $make->id;
                }    
                $model = HorseModel::where('name', $row['model'])->get()->first();
                if (isset($model)) {
                    $model_id = $model->id;
                }    
              
                if (isset($transporter_id) && $transporter_id != "") {
                    $horse->transporter_id     = $transporter_id;
                }
                if (isset($make_id) && $make_id != "") {
                    $horse->horse_make_id     = $make_id;
                }else {
                    $make = new HorseMake;
                    $make->name = $row['make'];
                    $make->save();
                    $horse->horse_make_id = $make->id;
                    $make_id = $make->id;
                }
                if (isset($model_id) && $model_id != "") {
                    $horse->horse_model_id     = $model_id;
                }else {
                    $model = new HorseModel;
                    $model->name = $row['model'];
                    $model->save();
                    $horse->horse_model_id = $model->id;
                    $model_id = $model->id;
                }
                $horse->chasis_number    = $row['chasisnumber'];
                $horse->engine_number    = $row['enginenumber'];
                $horse->registration_number     = $row['registration_number'];
                $horse->fleet_number     = $row['fleetnumber'];
                $horse->year    = $row['year'];
                $horse->color    = $row['color'];
                $horse->manufacturer =  $row['manufacturer'];
                $horse->country_of_origin    = $row['country_of_origin'];
                $horse->mileage   = $row['mileage'];
                $horse->fuel_consumption_empty_standard    = $row['fuel_consumption_empty'] ? $row['fuel_consumption_empty'] : 0.5;
                $horse->fuel_consumption_loaded_standard    = $row['fuel_consumption_loaded'] ? $row['fuel_consumption_loaded'] : 0.5;
                $horse->update();
                $transporter_id = "";
                $make_id = "";
                $model_id = "";
                
               
      
            } else {
               
            $transporter = Transporter::where('transporter_number', $row['transporter_number'])->get()->first();
            if (isset($transporter)) {
                $transporter_id = $transporter->id;
            }    
            $make = HorseMake::where('name', $row['make'])->get()->first();
            if (isset($make)) {
                $make_id = $make->id;
            }    
            $model = HorseModel::where('name', $row['model'])->get()->first();
            if (isset($model)) {
                $model_id = $model->id;
            }    
            $horse = new Horse;
            $horse->user_id     = Auth::user()->id;
            if (isset($transporter_id) && $transporter_id != "") {
                $horse->transporter_id = $transporter_id;
            }
            if (isset($make_id) && $make_id != "") {
                $horse->horse_make_id = $make_id;
            }else {
                $make = new HorseMake;
                $make->name = $row['make'];
                $make->save();
                $horse->horse_make_id = $make->id;
                $make_id = $make->id;
            }
            if (isset($model_id) && $model_id != "") {
                $horse->horse_model_id = $model_id;
            }else {
                $model = new HorseModel;
                $model->name = $row['model'];
                $model->save();
                $horse->horse_model_id = $model->id;
                $model_id = $model->id;
            }
            $horse->chasis_number    = $row['chasisnumber'];
            $horse->engine_number    = $row['enginenumber'];
            $horse->registration_number     = $row['registration_number'];
            $horse->fleet_number     = $row['fleetnumber'];
            $horse->horse_number     =  $this->horseNumber();
            $horse->year    = $row['year'];
            $horse->color    = $row['color'];
            $horse->manufacturer =  $row['manufacturer'];
            $horse->country_of_origin    = $row['country_of_origin'];
            $horse->mileage   = $row['mileage'];
            $horse->fuel_consumption_empty_standard    = $row['fuel_consumption_empty'] ? $row['fuel_consumption_empty'] : 0.5;
            $horse->fuel_consumption_loaded_standard    = $row['fuel_consumption_loaded'] ? $row['fuel_consumption_loaded'] : 0.5;
            $horse->save();
            $transporter_id = "";
            $make_id = "";
            $model_id = "";
            }
            

    }
       }

    }

    public function rules(): array{
        return[
            // '*.transporter_number' => ['required'],
            // '*.registration_number' => ['required','unique:horses,registration_number,NULL,id,deleted_at,NULL'],
            // '*.chasis_number' => ['nullable','unique:horses,chasis_number,NULL,id,deleted_at,NULL'],
            // '*.fleet_number' => ['nullable','unique:horses,fleet_number,NULL,id,deleted_at,NULL'],
            // '*.engine_number' => ['nullable','unique:horses,engine_number,NULL,id,deleted_at,NULL']
        ];
    }




    public function chunkSize(): int
    {
        return 1000;
    }
}
