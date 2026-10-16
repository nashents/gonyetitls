<?php

namespace App\Imports;

use App\Imports\Concerns\SkipsTemplateExampleRows;

use App\Models\User;
use App\Models\Count;
use App\Models\Consignor;
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

class ConsignorsImport implements  ToCollection, SkipsEmptyRows, WithLimit, 
WithHeadingRow,
SkipsOnError,
WithValidation,
WithChunkReading,
WithBatchInserts
{
    use SkipsTemplateExampleRows;

    use Importable, SkipsErrors;

 

    public function consignorNumber(){

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

            $consignor = Consignor::orderBy('id', 'desc')->first();

        if (!$consignor) {
            $consignor_number =  $initials .'CR'. str_pad(1, 5, "0", STR_PAD_LEFT);
        }else {
            $number = $consignor->id + 1;
            $consignor_number =  $initials .'CR'. str_pad($number, 5, "0", STR_PAD_LEFT);
        }

        return  $consignor_number;


    }

     public function limit(): int
    {
        return 2500; // Import only the first 100 rows
    }
 

    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function collection(Collection $rows)
    {


       foreach($rows as $row){
        if($row->filter()->isNotEmpty()){
     
        $consignor = Consignor::where('name',$row['name'])->get()->first();


        if (isset($consignor)) {
          
            $consignor->name    = $row['name'];
            $consignor->email    = $row['email'];
            $consignor->phonenumber    = $row['phonenumber'];
            $consignor->worknumber    = $row['worknumber'];
            $consignor->country    = $row['country'];
            $consignor->city    = $row['city'];
            $consignor->suburb    = $row['suburb'];
            $consignor->street_address    = $row['streetaddress'];
            $consignor->update();
            
           
        } else {
             
       $consignor = new Consignor;
       $consignor->company_id     = Auth::user()->employee->company_id;
       $consignor->user_id     = Auth::user()->id;
       $consignor->consignor_number   = $this->consignorNumber();
       $consignor->name    = $row['name'];
       $consignor->email    = $row['email'];
       $consignor->phonenumber    = $row['phonenumber'];
       $consignor->worknumber    = $row['worknumber'];
       $consignor->country    = $row['country'];
       $consignor->city    = $row['city'];
       $consignor->suburb    = $row['suburb'];
       $consignor->street_address    = $row['streetaddress'];
       $consignor->save();
        }
        

    }
       }
    }

    public function rules(): array{
        return[
            // '*.name' => ['nullable','unique:users,name,NULL,id,deleted_at,NULL'],
            // '*.email' => ['nullable','unique:users,email,NULL,id,deleted_at,NULL'],
            // '*.phonenumber' => ['nullable','unique:consignors,phonenumber,NULL,id,deleted_at,NULL'],
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
