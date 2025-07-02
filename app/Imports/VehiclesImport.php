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

class VehiclesImport implements ToCollection, SkipsEmptyRows, WithLimit, 
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

     public function limit(): int
    {
        return 2500; // Import only the first 100 rows
    }

    public function collection(Collection $rows)
    {

        foreach($rows as $row){

                $registrationNumber = trim($row->get('registration_number', ''));

        if (empty($registrationNumber)) {
            \Log::warning('Skipped row due to empty registration_number:', $row->toArray());
            continue;
        }

        if ($row->filter()->isNotEmpty()) {
            $vehicle = Vehicle::firstOrNew(['registration_number' => $registrationNumber]);

            // Helper closure to get or create related record IDs
            $getOrCreateId = function ($model, $column, $value) {
                if (!$value) return null;

                $record = $model::where($column, $value)->first();
                if ($record) {
                    return $record->id;
                }

                $newRecord = new $model;
                $newRecord->$column = $value;
                $newRecord->save();
                return $newRecord->id;
            };

            $transporter_id = $getOrCreateId(Transporter::class, 'transporter_number', $row->get('transporter_number'));
            $make_id        = $getOrCreateId(VehicleMake::class, 'name', $row->get('make'));
            $model_id       = $getOrCreateId(VehicleModel::class, 'name', $row->get('model'));

            if (!$vehicle->exists) {
                $vehicle->user_id = Auth::user()->id;
                $vehicle->vehicle_number = $this->vehicleNumber();
            }

            $vehicle->transporter_id         = $transporter_id;
            $vehicle->vehicle_make_id        = $make_id;
            $vehicle->vehicle_model_id       = $model_id;
            $vehicle->chasis_number          = $row->get('chasisnumber');
            $vehicle->engine_number          = $row->get('enginenumber');
            $vehicle->registration_number    = $registrationNumber;
            $vehicle->fleet_number           = $row->get('fleetnumber');
            $vehicle->year                   = $row->get('year');
            $vehicle->color                  = $row->get('color');
            $vehicle->manufacturer           = $row->get('manufacturer');
            $vehicle->country_of_origin      = $row->get('country_of_origin');
            $vehicle->mileage                = $row->get('mileage');
            $vehicle->hours                  = $row->get('engine_hours');
            $vehicle->fuel_type              = $row->get('fueltype');
            $vehicle->fuel_consumption_empty_standard  = $row->get('fuel_consumption_empty', 0);
            $vehicle->fuel_consumption_loaded_standard = $row->get('fuel_consumption_loaded', 0);

            $vehicle->save();
        }
      
        }
    }

    public function rules(): array{
        return[
             '*.registration_number' => 'required|string',
            // '*.transporter_number' => ['required'],
            // '*.registration_number' => ['nullable','unique:vehicles,registration_number,NULL,id,deleted_at,NULL'],
            // '*.chasis_number' => ['nullable','unique:vehicles,chasis_number,NULL,id,deleted_at,NULL'],
            // '*.fleet_number' => ['nullable','unique:vehicles,vehicle_number,NULL,id,deleted_at,NULL'],
            // '*.engine_number' => ['nullable','unique:vehicles,engine_number,NULL,id,deleted_at,NULL']
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
