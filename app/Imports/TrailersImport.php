<?php

namespace App\Imports;

use App\Models\Trailer;
use App\Models\Vehicle;
use App\Models\Transporter;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class TrailersImport implements ToCollection,
WithHeadingRow,
SkipsOnError,
WithValidation,
WithChunkReading
{

    use Importable, SkipsErrors;
    public $transporter;
    public $transporter_id;
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */

    public function trailerNumber(){

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

            $trailer = Trailer::orderBy('id', 'desc')->first();

        if (!$trailer) {
            $trailer_number =  $initials .'T'. str_pad(1, 5, "0", STR_PAD_LEFT);
        }else {
            $number = $trailer->id + 1;
            $trailer_number =  $initials .'T'. str_pad($number, 5, "0", STR_PAD_LEFT);
        }

        return  $trailer_number;


    }
    public function collection(Collection $rows)
    {

       foreach($rows as $row){
        if($row->filter()->isNotEmpty()){


            $trailer = Trailer::where('registration_number',$row['registration_number'])->get()->first();

            if (isset($trailer)) {

                $transporter = Transporter::where('transporter_number',$row['transporter_number'])->get()->first();
                if (isset($transporter)) {
                    $transporter_id = $transporter->id;
                }    
                

            if (isset($transporter)) {
                $transporter_id = $transporter->id;
            }    
        
             if (isset($transporter_id) && $transporter_id != "") {
                $trailer->transporter_id     = $transporter_id;
            }
             $trailer->make     = $row['make'];
             $trailer->model    = $row['model'];
             $trailer->chasis_number    = $row['chasisnumber'];
             $trailer->registration_number     = $row['registration_number'];
             $trailer->fleet_number     = $row['fleetnumber'];
             $trailer->year    = $row['year'];
             $trailer->color    = $row['color'];
             $trailer->manufacturer =  $row['manufacturer'];
             $trailer->country_of_origin    = $row['country_of_origin'];
             $trailer->no_of_wheels    = $row['no_of_wheels'];
             $trailer->update();
             $transporter_id = "";
                
                
            } else {
                $transporter = Transporter::where('transporter_number',$row['transporter_number'])->get()->first();
                if (isset($transporter)) {
                    $transporter_id = $transporter->id;
                }    
                
                 $trailer = new Trailer;
                 $trailer->user_id     = Auth::user()->id;
                 if (isset($transporter_id) && $transporter_id != "") {
                    $trailer->transporter_id     = $transporter_id;
                }
                 $trailer->make     = $row['make'];
                 $trailer->model    = $row['model'];
                 $trailer->chasis_number    = $row['chasisnumber'];
                 $trailer->registration_number     = $row['registration_number'];
                 $trailer->trailer_number     = $this->trailerNumber();
                 $trailer->fleet_number     = $row['fleetnumber'];
                 $trailer->year    = $row['year'];
                 $trailer->color    = $row['color'];
                 $trailer->manufacturer =  $row['manufacturer'];
                 $trailer->country_of_origin    = $row['country_of_origin'];
                 $trailer->no_of_wheels    = $row['no_of_wheels'];
                 $trailer->save();
                 $transporter_id = "";
            }
            
        
         
        }
       }
    }

    public function rules(): array{
        return[
            '*.transporter_number' => ['required'],
            // '*.registration_number' => ['required','unique:trailers,registration_number,NULL,id,deleted_at,NULL'],
            // '*.chasis_number' => ['nullable','unique:trailers,chasis_number,NULL,id,deleted_at,NULL'],
        ];
    }




    public function chunkSize(): int
    {
        return 1000;
    }
}
