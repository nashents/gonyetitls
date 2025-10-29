<?php

namespace App\Imports;

use App\Models\User;
use App\Models\Count;
use App\Models\Consignee;
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

class ConsigneesImport implements  ToCollection, SkipsEmptyRows, WithLimit, 
WithHeadingRow,
SkipsOnError,
WithValidation,
WithChunkReading,
WithBatchInserts
{
    use Importable, SkipsErrors;

 

    public function consigneeNumber(){

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

            $consignee = Consignee::orderBy('id', 'desc')->first();

        if (!$consignee) {
            $consignee_number =  $initials .'C'. str_pad(1, 5, "0", STR_PAD_LEFT);
        }else {
            $number = $consignee->id + 1;
            $consignee_number =  $initials .'C'. str_pad($number, 5, "0", STR_PAD_LEFT);
        }

        return  $consignee_number;


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
     
        $consignee = Consignee::where('name',$row['name'])->get()->first();


        if (isset($consignee)) {
          
            $consignee->name    = $row['name'];
            $consignee->email    = $row['email'];
            $consignee->phonenumber    = $row['phonenumber'];
            $consignee->worknumber    = $row['worknumber'];
            $consignee->country    = $row['country'];
            $consignee->city    = $row['city'];
            $consignee->suburb    = $row['suburb'];
            $consignee->street_address    = $row['streetaddress'];
            $consignee->update();
            
           
        } else {
             
       $consignee = new Consignee;
       $consignee->company_id     = Auth::user()->employee->company_id;
       $consignee->user_id     = Auth::user()->id;
       $consignee->consignee_number   = $this->consigneeNumber();
       $consignee->name    = $row['name'];
       $consignee->email    = $row['email'];
       $consignee->phonenumber    = $row['phonenumber'];
       $consignee->worknumber    = $row['worknumber'];
       $consignee->country    = $row['country'];
       $consignee->city    = $row['city'];
       $consignee->suburb    = $row['suburb'];
       $consignee->street_address    = $row['streetaddress'];
       $consignee->save();
        }
        

    }
       }
    }

    public function rules(): array{
        return[
            // '*.name' => ['nullable','unique:users,name,NULL,id,deleted_at,NULL'],
            // '*.email' => ['nullable','unique:users,email,NULL,id,deleted_at,NULL'],
            // '*.phonenumber' => ['nullable','unique:consignees,phonenumber,NULL,id,deleted_at,NULL'],
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
