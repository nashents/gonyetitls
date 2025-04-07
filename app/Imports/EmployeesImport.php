<?php

namespace App\Imports;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Count;
use App\Models\Employee;
use App\Imports\EmployeesImport;
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

class EmployeesImport implements  ToCollection,
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

    public function employeeNumber(){

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

            $employee = Employee::orderBy('id', 'desc')->first();

        if (!$employee) {
            $employee_number =  $initials .'E'. str_pad(1, 5, "0", STR_PAD_LEFT);
        }else {
            $number = $employee->id + 1;
            $employee_number =  $initials .'E'. str_pad($number, 5, "0", STR_PAD_LEFT);
        }

        return  $employee_number;


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
        $employee = Employee::where('name',$row['name'])
                        ->where('surname',$row['surname'])->get()->first();

     
        if (isset($user) && isset($employee)) {

            $user->name = $row['name'];
            $user->surname = $row['surname'];
            $user->category  = 'employee';
            $user->is_admin = '0';
            $user->active = '1';
            $user->email = $row['email'];
            $user->update();
            $user->roles()->attach([3]);
    
       
            $employee->name    = $row['name'];
            $employee->surname     = $row['surname'];
            $employee->gender     = $row['gender'];
            $employee->dob     = isset($row['dob']) ? Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['dob'])) : null;
            $employee->email    = $row['email'];
            $employee->phonenumber    = $row['phonenumber'];
            $employee->idnumber    = $row['idnumber'];
            $employee->country    = $row['country'];
            $employee->city    = $row['city'];
            $employee->suburb    = $row['suburb'];
            $employee->duration    = $row['contract_duration'];
            $employee->start_date    = isset($row['start_date']) ? Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['start_date'])) : null;
            $employee->expiration    = isset($row['expiry_date']) ? Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['expiry_date'])) : null;
            $employee->street_address    = $row['streetaddress'];
            $employee->next_of_kin    = $row['nextofkin'];
            $employee->relationship    = $row['relationship'];
            $employee->contact    = $row['contact'];
            $employee->update();
            $employee->ranks()->attach([4]);
            
 
        } else {
            $user = User::create([
                'name'     => $row['name'],
                'category'     => 'employee',
                'surname'     => $row['surname'],
                'is_admin'     => '0',
                'active'     => '1',
                'email'     => $row['email'],
                'password'    => Hash::make($pin),
    
            ]);
            $user->roles()->attach([3]);
    
           $employee = new Employee;
           if (isset(Auth::user()->company)) {
                $employee->company_id     = Auth::user()->company->id;
           }elseif (isset(Auth::user()->employee->company)) {
                $employee->company_id     = Auth::user()->employee->company->id;
           }
           $employee->creator_id     = Auth::user()->id;
           $employee->user_id     = $user->id;
           $employee->employee_number   = $this->employeeNumber();
           $employee->name    = $row['name'];
           $employee->surname     = $row['surname'];
           $employee->gender     = $row['gender'];
           $employee->dob     = isset($row['dob']) ? Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['dob'])) : null;
           $employee->email    = $row['email'];
           $employee->pin    = $pin;
           $employee->phonenumber    = $row['phonenumber'];
           $employee->idnumber    = $row['idnumber'];
           $employee->country    = $row['country'];
           $employee->city    = $row['city'];
           $employee->suburb    = $row['suburb'];
           $employee->duration    = $row['contract_duration'];
           $employee->start_date    = isset($row['start_date']) ? Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['start_date'])) : null;
           $employee->expiration    = isset($row['expiry_date']) ? Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['expiry_date'])) : null;
           $employee->street_address    = $row['streetaddress'];
           $employee->next_of_kin    = $row['nextofkin'];
           $employee->relationship    = $row['relationship'];
           $employee->contact    = $row['contact'];
           $employee->save();
           $employee->ranks()->attach([4]);
        }
        

    }
       }
    }

    public function rules(): array{
        return[
            // '*.idnumber' => ['nullable','unique:employees,idnumber,NULL,id,deleted_at,NULL'],
        ];
    }




    public function chunkSize(): int
    {
        return 1000;
    }
}
