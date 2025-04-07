<?php

namespace App\Imports;

use App\Models\User;
use App\Models\Count;
use App\Models\Broker;
use App\Imports\BrokersImport;
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

class BrokersImport implements  ToCollection,
WithHeadingRow,
SkipsOnError,
WithValidation,
WithChunkReading
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

    public function brokerNumber(){

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

            $broker = Broker::orderBy('id', 'desc')->first();

        if (!$broker) {
            $broker_number =  $initials .'B'. str_pad(1, 5, "0", STR_PAD_LEFT);
        }else {
            $number = $broker->id + 1;
            $broker_number =  $initials .'B'. str_pad($number, 5, "0", STR_PAD_LEFT);
        }

        return  $broker_number;


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
        $broker = Broker::where('name',$row['name'])->get()->first();

        if (isset($user) && isset($broker)) {
           
          
            $user->name = $row['name'];
            $user->category  = 'broker';
            $user->is_admin = '0';
            $user->active = '1';
            $user->email = $row['email'];
            $user->update();
            $user->roles()->attach([3]);
    
            $broker->name    = $row['name'];
            $broker->email    = $row['email'];
            $broker->phonenumber    = $row['phonenumber'];
            $broker->worknumber    = $row['worknumber'];
            $broker->country    = $row['country'];
            $broker->city    = $row['city'];
            $broker->suburb    = $row['suburb'];
            $broker->street_address    = $row['streetaddress'];
            $broker->status    = 1;
            $broker->update();

         
        } else {
           
        $user = User::create([
            'name'     => $row['name'],
            'category'     => 'broker',
            'is_admin'     => '0',
            'active'     => '1',
            'email'     => $row['email'],
            'password'    => Hash::make($pin),

        ]);
        $user->roles()->attach([3]);
      
       $broker = new Broker;
       $broker->company_id     = Auth::user()->employee->company_id;
       $broker->creator_id     = Auth::user()->id;
       $broker->user_id     = $user->id;
       $broker->broker_number   = $this->brokerNumber();
       $broker->name    = $row['name'];
       $broker->email    = $row['email'];
       $broker->pin    = $pin;
       $broker->phonenumber    = $row['phonenumber'];
       $broker->worknumber    = $row['worknumber'];
       $broker->country    = $row['country'];
       $broker->city    = $row['city'];
       $broker->suburb    = $row['suburb'];
       $broker->street_address    = $row['streetaddress'];
       $broker->status    = 1;
       $broker->save();
        }
        

    }
       }
    }

    public function rules(): array{
        return[
            '*.name' => ['required'],
        ];
    }

    public function chunkSize(): int
    {
        return 1000;
    }
}
