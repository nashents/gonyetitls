<?php

namespace App\Imports;

use App\Models\User;
use App\Models\Count;
use App\Models\OffloadingPoint;
use App\Imports\OffloadingPointsImport;
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

class OffloadingPointsImport implements  ToCollection,
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

            $offloading_point = OffloadingPoint::where('name',$row['name'])->get()->first();

            if (isset($offloading_point)) {

                
                $offloading_point->name     = $row['name'];
                $offloading_point->lat     = $row['latitude'];
                $offloading_point->long     = $row['longitude'];
                $offloading_point->contact_name     = $row['contact_name'];
                $offloading_point->contact_surname     = $row['contact_surname'];
                $offloading_point->email     = $row['email'];
                $offloading_point->phonenumber     = $row['phonenumber'];
                $offloading_point->update();
                
              
               
            } else {
            $offloading_point = new OffloadingPoint;
            $offloading_point->user_id     = Auth::user()->id;
            $offloading_point->name     = $row['name'];
            $offloading_point->lat     = $row['latitude'];
            $offloading_point->long     = $row['longitude'];
            $offloading_point->contact_name     = $row['contact_name'];
            $offloading_point->contact_surname     = $row['contact_surname'];
            $offloading_point->email     = $row['email'];
            $offloading_point->phonenumber     = $row['phonenumber'];
            $offloading_point->save();
            }
            

           
            
    }
       }
    }

    public function rules(): array{
        return[
            // '*.name' => ['required','unique:offloading_points,name,NULL,id,deleted_at,NULL'],
        ];
    }




    public function chunkSize(): int
    {
        return 1000;
    }
}
