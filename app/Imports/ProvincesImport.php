<?php

namespace App\Imports;

use App\Models\User;
use App\Models\Count;
use App\Models\Country;
use App\Models\Province;
use App\Models\LoadingPoint;
use App\Imports\ProvincesImport;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
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

class ProvincesImport implements  ToCollection, SkipsEmptyRows, WithLimit, 
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

     public function limit(): int
    {
        return 2500; // Import only the first 100 rows
    }
    
    public function collection(Collection $rows)
    {


       foreach($rows as $row){

            if($row->filter()->isNotEmpty()){

                $country = Country::where('name',$row['country'])->get()->first();

                $province = Province::where('name',$row['province'])->get()->first();

                $countryName = $row->get('country');
                $provinceName = $row->get('province');

                // Find or create the country
                $country = Country::firstOrCreate(
                    ['name' => $countryName],
                    ['user_id' => Auth::id()]
                );
                
                // Find the province by name
                $province = Province::where('name', $provinceName)->first();

                if ($province) {
                    
                    // Update existing province
                    $province->fill([
                        'user_id' => Auth::id(),
                        'country_id' => $country->id,
                        'name' => $provinceName,
                    ])->save();

                } else {
                    // Create new province
                    Province::create([
                        'user_id' => Auth::id(),
                        'country_id' => $country->id,
                        'name' => $provinceName,
                    ]);

                }

            }   

       }
    }

    public function rules(): array{
        return[
            // '*.name' => ['required','unique:loading_points,name,NULL,id,deleted_at,NULL'],
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
