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

class HorsesImport implements ToCollection, SkipsEmptyRows, WithLimit, 
WithHeadingRow,
SkipsOnError,
WithValidation,
WithChunkReading,
WithBatchInserts
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

     public function limit(): int
    {
        return 2500; // Import only the first 100 rows
    }
    
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
        foreach ($rows as $row) {
            if ($row->filter()->isEmpty()) {
                continue;
            }

            $registrationNumber = $row->get('registration_number');
            $transporterNumber = $row->get('transporter_number');
            $makeName = $row->get('make');
            $modelName = $row->get('model');

            $horse = Horse::firstOrNew(['registration_number' => $registrationNumber]);

            $transporter = Transporter::where('transporter_number', $transporterNumber)->first();
            if ($transporter) {
                $horse->transporter_id = $transporter->id;
            }

            $make = HorseMake::firstOrCreate(['name' => $makeName]);
            $horse->horse_make_id = $make->id;

            $model = HorseModel::firstOrCreate(['name' => $modelName]);
            $horse->horse_model_id = $model->id;

            // Always set user_id only if creating a new record
            if (!$horse->exists) {
                $horse->user_id = Auth::id();
                $horse->horse_number = $this->horseNumber();
            }

            // Set other fields
            $horse->chasis_number = $row->get('chasisnumber');
            $horse->engine_number = $row->get('enginenumber');
            $horse->fleet_number = $row->get('fleetnumber');
            $horse->year = $row->get('year');
            $horse->color = $row->get('color');
            $horse->manufacturer = $row->get('manufacturer');
            $horse->country_of_origin = $row->get('country_of_origin');
            $horse->mileage = $row->get('mileage');
            $horse->hours = $row->get('engine_hours');
            $horse->fuel_consumption_empty_standard = $row->get('fuel_consumption_empty', 0.5);
            $horse->fuel_consumption_loaded_standard = $row->get('fuel_consumption_loaded', 0.5);

            // Save new or update existing
            $horse->save();
        }
    }

    public function rules(): array{
        return[
             '*.registration_number' => 'required|string',
            // '*.transporter_number' => ['required'],
            // '*.registration_number' => ['required','unique:horses,registration_number,NULL,id,deleted_at,NULL'],
            // '*.chasis_number' => ['nullable','unique:horses,chasis_number,NULL,id,deleted_at,NULL'],
            // '*.fleet_number' => ['nullable','unique:horses,fleet_number,NULL,id,deleted_at,NULL'],
            // '*.engine_number' => ['nullable','unique:horses,engine_number,NULL,id,deleted_at,NULL']
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
