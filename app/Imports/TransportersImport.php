<?php

namespace App\Imports;

use App\Models\User;
use App\Models\Count;
use App\Models\Transporter;
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

class TransportersImport implements  ToCollection, SkipsEmptyRows, WithLimit, 
WithHeadingRow,
SkipsOnError,
WithValidation,
WithChunkReading,
WithBatchInserts
{
    use Importable, SkipsErrors;

    public function generatePIN($digits = 4){
        $i = 0; //counter
        $pin = ""; //our default pin is blank.
        while($i < $digits){
            //generate a random number between 0 and 9.
            $pin .= mt_rand(0, 9);
            $i++;
        }
        return $pin;
    }

    public function transporterNumber(){

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

            $transporter = Transporter::orderBy('id', 'desc')->first();

        if (!$transporter) {
            $transporter_number =  $initials .'T'. str_pad(1, 5, "0", STR_PAD_LEFT);
        }else {
            $number = $transporter->id + 1;
            $transporter_number =  $initials .'T'. str_pad($number, 5, "0", STR_PAD_LEFT);
        }

        return  $transporter_number;


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
            

        $pin =  $this->generatePIN();
        $user = User::where('name',$row['name'])->get()->first();
        $transporter = Transporter::where('name',$row['name'])->get()->first();

    if (isset($user) && isset($transporter)) {
        
        $user->name = $row['name'];
        $user->category  = 'transporter';
        $user->is_admin = '0';
        $user->active = '1';
        $user->email = $row['email'];
        $user->update();
        $user->roles()->attach([3]);
    
        $transporter->name = $row['name'];
        $transporter->email = $row['email'];
        $transporter->phonenumber    = $row['phonenumber'];
        $transporter->worknumber    = $row['worknumber'];
        $transporter->country    = $row['country'];
        $transporter->city    = $row['city'];
        $transporter->suburb    = $row['suburb'];
        $transporter->street_address    = $row['streetaddress'];
        $transporter->authorization    = "approved";
        $transporter->update();
        
    }else {
        $user = User::create([
            'name'     => $row['name'],
            'category'     => 'transporter',
            'is_admin'     => '0',
            'active'     => '1',
            'email'     => $row['email'],
            'password'    => Hash::make($pin),

        ]);
        $user->roles()->attach([3]);
      
       $transporter = new Transporter;
       $transporter->company_id     = Auth::user()->employee->company_id;
       $transporter->creator_id     = Auth::user()->id;
       $transporter->user_id     = $user->id;
       $transporter->transporter_number   = $this->transporterNumber();
       $transporter->name    = $row['name'];
       $transporter->email    = $row['email'];
       $transporter->pin    = $pin;
       $transporter->phonenumber    = $row['phonenumber'];
       $transporter->worknumber    = $row['worknumber'];
       $transporter->country    = $row['country'];
       $transporter->city    = $row['city'];
       $transporter->suburb    = $row['suburb'];
       $transporter->street_address    = $row['streetaddress'];
       $transporter->authorization    = "approved";
       $transporter->save();
    }
  
    }
       }
    }

    public function rules(): array{
        return[
            '*.name' => ['required'],
            // '*.email' => ['nullable','unique:users,email,NULL,id,deleted_at,NULL'],
            // '*.phonenumber' => ['nullable','unique:transporters,phonenumber,NULL,id,deleted_at,NULL'],
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
