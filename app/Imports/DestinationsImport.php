<?php

namespace App\Imports;

use App\Models\User;
use App\Models\Count;
use App\Models\Country;
use App\Models\Destination;
use Illuminate\Support\Collection;
use App\Imports\DestinationsImport;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class DestinationsImport implements  ToCollection,
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
    public function collection(Collection $rows)
    {


       foreach($rows as $row){
        if($row->filter()->isNotEmpty()){
            $country = Country::where('name', $row['country'])->get()->first();

            if (isset($country)) {
                $country_id = $country->id;
            }  

            $destination = Destination::where('city',$row['city'])->get()->first();

            if (isset($destination)) {

                if (isset($country_id) && $country_id != "") {
                    $destination->country_id = $country_id;
                }
                $destination->city     = $row['city'];
                $destination->lat     = $row['latitude'];
                $destination->long     = $row['longitude'];
                $destination->description     = $row['description'];
                $destination->update();

                $country_id = "";
                
          
            } else {
                $destination = new Destination;
                $destination->user_id     = Auth::user()->id;
                if (isset($country_id) && $country_id != "") {
                    $destination->country_id = $country_id;
                }
                $destination->city     = $row['city'];
                $destination->lat     = $row['latitude'];
                $destination->long     = $row['longitude'];
                $destination->description     = $row['description'];
                $destination->save();
                $country_id = "";
            }

           
            
    }
       }
    }

    public function rules(): array{
        return[
            '*.city' => ['required','unique:destinations,city,NULL,id,deleted_at,NULL'],
            '*.country' => ['required'],
        ];
    }




    public function chunkSize(): int
    {
        return 1000;
    }
}
