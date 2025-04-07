<?php

namespace App\Imports;

use App\Models\User;
use App\Models\Count;
use App\Models\Country;
use App\Imports\CountriesImport;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class CountriesImport implements  ToCollection,
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

            $country = Country::where('name',$row['name'])->get()->first();

            if (isset($country)) {
              
                $country->name = $row['name'];
                $country->update();
              
            } else {
                $country = new Country;
                $country->user_id     = Auth::user()->id;
                $country->name     = $row['name'];
                $country->save();
            }
            
          
            
    }
       }
    }

    public function rules(): array{
        return[
            // '*.name' => ['required','unique:countries,name,NULL,id,deleted_at,NULL'],
        ];
    }




    public function chunkSize(): int
    {
        return 1000;
    }
}
