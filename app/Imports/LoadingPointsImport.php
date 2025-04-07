<?php

namespace App\Imports;

use App\Models\User;
use App\Models\Count;
use App\Models\LoadingPoint;
use App\Imports\LoadingPointsImport;
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

class LoadingPointsImport implements  ToCollection,
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

            $loading_point = LoadingPoint::where('name',$row['name'])->get()->first();

            if (isset($loading_point)) {
              
                $loading_point->name     = $row['name'];
                $loading_point->lat     = $row['latitude'];
                $loading_point->long     = $row['longitude'];
                $loading_point->contact_name     = $row['contact_name'];
                $loading_point->contact_surname     = $row['contact_surname'];
                $loading_point->email     = $row['email'];
                $loading_point->phonenumber     = $row['phonenumber'];
                $loading_point->update();
                
            } else {
            $loading_point = new LoadingPoint;
            $loading_point->user_id     = Auth::user()->id;
            $loading_point->name     = $row['name'];
            $loading_point->lat     = $row['latitude'];
            $loading_point->long     = $row['longitude'];
            $loading_point->contact_name     = $row['contact_name'];
            $loading_point->contact_surname     = $row['contact_surname'];
            $loading_point->email     = $row['email'];
            $loading_point->phonenumber     = $row['phonenumber'];
            $loading_point->save();
            }
            
           
            
    }
       }
    }

    public function rules(): array{
        return[
            // '*.name' => ['required','unique:loading_points,name,NULL,id,deleted_at,NULL'],
        ];
    }




    public function chunkSize(): int
    {
        return 1000;
    }
}
