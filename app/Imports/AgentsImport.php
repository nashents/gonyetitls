<?php

namespace App\Imports;

use App\Models\User;
use App\Models\Count;
use App\Models\Agent;
use App\Imports\AgentsImport;
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

class AgentsImport implements  ToCollection,
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

    public function agentNumber(){

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

            $agent = Agent::orderBy('id', 'desc')->first();

        if (!$agent) {
            $agent_number =  $initials .'A'. str_pad(1, 5, "0", STR_PAD_LEFT);
        }else {
            $number = $agent->id + 1;
            $agent_number =  $initials .'A'. str_pad($number, 5, "0", STR_PAD_LEFT);
        }

        return  $agent_number;


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
     
        $user = User::where('name',$row['name'])
                    ->where('surname',$row['surname'])->get()->first();
        $agent = Agent::where('name',$row['name'])
                      ->where('surname',$row['surname'])->get()->first();

        if (isset($user) && isset($agent)) {

        $user->name = $row['name'];
        $user->surname = $row['surname'];
        $user->category  = 'agent';
        $user->is_admin = '0';
        $user->active = '1';
        $user->email = $row['email'];
        $user->update();
        $user->roles()->attach([3]);
       
        $agent->name    = $row['name'];
        $agent->surname     = $row['surname'];
        $agent->gender     = $row['gender'];
        $agent->dob     = $row['dob'];
        $agent->email    = $row['email'];
        $agent->phonenumber    = $row['phonenumber'];
        $agent->idnumber    = $row['idnumber'];
        $agent->country    = $row['country'];
        $agent->city    = $row['city'];
        $agent->suburb    = $row['suburb'];
        $agent->province    = $row['province'];
        $agent->street_address    = $row['streetaddress'];
        $agent->update();
   
        }else {
                $user = User::create([
                'name'     => $row['name'],
                'category'     => 'agent',
                'surname'     => $row['surname'],
                'is_admin'     => '0',
                'active'     => '1',
                'email'     => $row['email'],
                'password'    => Hash::make($pin),
    
            ]);
            $user->roles()->attach([3]);
    
           $agent = new Agent;
           $agent->company_id     = Auth::user()->employee->company_id;
           $agent->creator_id     = Auth::user()->id;
           $agent->user_id     = $user->id;
           $agent->agent_number   = $this->agentNumber();
           $agent->name    = $row['name'];
           $agent->surname     = $row['surname'];
           $agent->gender     = $row['gender'];
           $agent->dob     = $row['dob'];
           $agent->email    = $row['email'];
           $agent->pin    = $pin;
           $agent->phonenumber    = $row['phonenumber'];
           $agent->idnumber    = $row['idnumber'];
           $agent->country    = $row['country'];
           $agent->city    = $row['city'];
           $agent->suburb    = $row['suburb'];
           $agent->province    = $row['province'];
           $agent->street_address    = $row['streetaddress'];
           $agent->save();
        }

    }
       }
    }

    public function rules(): array{
        return[
            // '*.idnumber' => ['nullable','unique:agents,idnumber,NULL,id,deleted_at,NULL'],
            // '*.email' => ['nullable','unique:users,email,NULL,id,deleted_at,NULL'],
        ];
    }




    public function chunkSize(): int
    {
        return 1000;
    }
}
