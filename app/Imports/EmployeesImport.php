<?php

namespace App\Imports;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Count;
use App\Models\Employee;
use App\Models\JobTitle;
use App\Models\EmployeePosition;
use App\Mail\AccountCreationMail;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
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

class EmployeesImport implements  ToCollection,SkipsEmptyRows, WithLimit, 
WithHeadingRow,
SkipsOnError,
WithValidation,
WithChunkReading,
WithBatchInserts
{
    use Importable, SkipsErrors;

    public $company;

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

    public function __construct()
        {
            if (optional(Auth::user()->company)) {
                $this->company = Auth::user()->company;
            } elseif (optional(Auth::user()->employee->company)) {
                $this->company = Auth::user()->employee->company;
            }
        }


    public function limit(): int
    {
        return 2500; // Import only the first 100 rows
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

      private function parseExcelDate($value)
        {
            if (!isset($value)) {
                return null;
            }

            // If it's a numeric Excel date serial
            if (is_numeric($value)) {
                try {
                    return Carbon::instance(
                        \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value)
                    );
                } catch (\Exception $e) {
                    return null;
                }
            }

            // If it's a string in strict YYYY-MM-DD format
            if (is_string($value)) {
                try {
                    $parsed = Carbon::createFromFormat('Y-m-d', $value);
                    return $parsed && $parsed->format('Y-m-d') === $value ? $parsed : null;
                } catch (\Exception $e) {
                    return null;
                }
            }

            return null;
        }

    private function parseGender($value)
    {
        $gender = ucfirst(strtolower(trim($value)));

        return in_array($gender, ['Male', 'Female']) ? $gender : null;
    }


    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function collection(Collection $rows)
    {

         foreach ($rows as $row) {

                // Skip completely empty rows
                if ($row->filter()->isEmpty()) {
                    continue;
                }

                $name    = $row->get('name');
                $surname = $row->get('surname');

                if (!$name || !$surname) {
                    // Skip row if required fields are missing
                    continue;
                }

                $email          = $row->get('email');
                $gender         = $row->get('gender');
                $dob            = $row->get('dob');
                $phone          = $row->get('phonenumber');
                $idNumber       = $row->get('idnumber');
                $country        = $row->get('country');
                $city           = $row->get('city');
                $suburb         = $row->get('suburb');
                $contract       = $row->get('contract_duration');
                $startDate      = $row->get('start_date');
                $expiryDate     = $row->get('expiry_date');
                $streetAddress  = $row->get('streetaddress');
                $nextOfKin      = $row->get('nextofkin');
                $relationship   = $row->get('relationship');
                $contact        = $row->get('contact');

                DB::transaction(function () use ($row,$name,$surname,$email,$gender,$dob,$phone,$idNumber,
                    $country,$city,$suburb,$contract,$startDate,$expiryDate,$streetAddress,$nextOfKin,$relationship,$contact
                ) {
                    $pin = $this->generatePIN();

                    // Find or instantiate by name + surname
                    $user = User::firstOrNew([
                        'name'    => $name,
                        'surname' => $surname,
                    ]);

                    $employee = Employee::firstOrNew([
                        'name'    => $name,
                        'surname' => $surname,
                    ]);

                    // Check if both existed already (mirrors your old if(isset($user) && isset($employee)))
                    $existingPair = $user->exists && $employee->exists;

                    /*
                    |----------------------
                    | USER FIELDS
                    |----------------------
                    */
                    $isNewUser = !$user->exists;

                    $user->category = 'employee';
                    $user->is_admin = '0';
                    $user->active   = '1';
                    $user->email    = $email;

                    if ($isNewUser) {
                        $user->password = Hash::make($pin);
                    }

                    $user->save();

                    // Avoid duplicate pivot records
                    $user->roles()->syncWithoutDetaching([3]);

                    /*
                    |----------------------
                    | EMPLOYEE FIELDS
                    |----------------------
                    */
                    $isNewEmployee = !$employee->exists;

                    if ($isNewEmployee) {
                        if (optional(Auth::user()->company)->id) {
                            $employee->company_id = Auth::user()->company->id;
                        } elseif (optional(Auth::user()->employee->company)->id) {
                            $employee->company_id = Auth::user()->employee->company->id;
                        }

                        $employee->creator_id       = Auth::id();
                        $employee->user_id          = $user->id;
                        $employee->employee_number  = $this->employeeNumber();
                        $employee->pin              = $pin;
                    }

                    $employee->name          = $name;
                    $employee->surname       = $surname;
                    $employee->gender        = $this->parseGender($gender);
                    $employee->dob           = $this->parseExcelDate($dob);
                    $employee->email         = $email;
                    $employee->phonenumber   = $phone;
                    $employee->idnumber      = $idNumber;
                    $employee->country       = $country;
                    $employee->city          = $city;
                    $employee->suburb        = $suburb;
                    $employee->duration      = is_numeric($contract) ? (int) $contract : null;
                    $employee->start_date    = $this->parseExcelDate($startDate);
                    $employee->expiration    = $this->parseExcelDate($expiryDate);
                    $employee->street_address = $streetAddress;
                    $employee->next_of_kin   = $nextOfKin;
                    $employee->relationship  = $relationship;
                    $employee->contact       = $contact;

                    $employee->save();

                    if (!empty($employee->email) && filter_var($employee->email, FILTER_VALIDATE_EMAIL)) {
                         Mail::to($employee->email)->send(new AccountCreationMail($user, $this->company,$pin));
                    }

                    // Avoid duplicate pivot records
                    $employee->ranks()->syncWithoutDetaching([3]);

                    /*
                    |----------------------
                    | EMPLOYEE POSITION
                    | Only when both user & employee existed already,
                    | same behaviour as your original code
                    |----------------------
                    */
                    if ($existingPair) {
                        $employeePosition = new EmployeePosition;
                        $employeePosition->employee_id   = $employee->id;
                        $employeePosition->job_title_id  = JobTitle::where('title', $employee->post)->first()?->id ?? null;
                        $employeePosition->rank_id       = $employee->ranks->first()?->id ?? null;
                        $employeePosition->branch_id     = $employee->branch_id ?? null;
                        $employeePosition->department_id = $employee->departments->first()?->id ?? null;
                        $employeePosition->grade_id      = $employee->grade_id ?? null;
                        $employeePosition->start_date    = $employee->start_date ?? null;
                        $employeePosition->changed_by    = Auth::id();
                        $employeePosition->change_reason = 'Appointment';
                        $employeePosition->remarks       = 'Initial Appointment';
                        $employeePosition->save();
                    }
                });
            }
      
    }

    public function rules(): array{
        return[
            // '*.idnumber' => ['nullable','unique:employees,idnumber,NULL,id,deleted_at,NULL'],
        ];
    }


 

       public function batchSize(): int
    {
       return 150;
    }

    public function chunkSize(): int
    {
        return 150;
    }
}
