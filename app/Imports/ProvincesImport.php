<?php

namespace App\Imports;

use App\Models\Country;
use App\Models\Province;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class ProvincesImport implements  ToCollection,
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

            $country = Country::where('name',$row['country'])->get()->first();
            $province = Province::where('name',$row['province'])->get()->first();

            if (isset($province)) {
                if (isset($country)) {
                    $province->user_id = Auth::user()->id;
                    $province->country_id = $country->id;
                    $province->name = $row['province'];
                    $province->update();
                  
                } else {

                    $country = new Country;
                    $country->user_id = Auth::user()->id;
                    $country->name  = $row['name'];
                    $country->save();
    
                    $province->user_id = Auth::user()->id;
                    $province->country_id = $country->id;
                    $province->name = $row['province'];
                    $province->update();
    
                }
            }else {
                if (isset($country)) {
                    $province = new Province;
                    $province->user_id = Auth::user()->id;
                    $province->country_id = $country->id;
                    $province->name = $row['province'];
                    $province->save();
                  
                } else {
                    $country = new Country;
                    $country->user_id = Auth::user()->id;
                    $country->name  = $row['country'];
                    $country->save();
    
                    $province = new Province;
                    $province->user_id = Auth::user()->id;
                    $province->country_id = $country->id;
                    $province->name = $row['province'];
                    $province->save();
    
                }
            }
         
            
    }
       }
    }

    public function rules(): array{
        return[
            '*.province' => ['required'],
            '*.country' => ['required'],
        ];
    }




    public function chunkSize(): int
    {
        return 1000;
    }
}
