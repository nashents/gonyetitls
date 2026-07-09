<?php

namespace App\Imports;

use App\Imports\EmployeesImport;
use App\Mail\AccountCreationMail;
use App\Models\Count;
use App\Models\Department;
use App\Models\Driver;
use App\Models\Employee;
use App\Models\EmployeeLeave;
use App\Models\EmployeePosition;
use App\Models\JobTitle;
use App\Models\Leave;
use App\Models\LeaveType;
use App\Models\Transporter;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithLimit;
use Maatwebsite\Excel\Concerns\WithValidation;

class DriversImport implements  ToCollection, SkipsEmptyRows, WithLimit,
WithHeadingRow,
SkipsOnError,
WithValidation,
WithChunkReading,
WithCalculatedFormulas,
WithBatchInserts
{
    use Importable, SkipsErrors;
    public $transporter;
    public $transporter_id;
    public $company;
    public $send_creds;

     public function __construct($send_creds)
        {

            $this->send_creds = $send_creds;
            if (Auth::user()->company) {
                $this->company = Auth::user()->company;
            } elseif (optional(Auth::user()->employee)->company) {
                $this->company = Auth::user()->employee->company;
            }
        }


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
    public function driverNumber(){

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

            $driver = Driver::orderBy('id', 'desc')->first();

        if (!$driver) {
            $driver_number =  $initials .'D'. str_pad(1, 5, "0", STR_PAD_LEFT);
        }else {
            $number = $driver->id + 1;
            $driver_number =  $initials .'D'. str_pad($number, 5, "0", STR_PAD_LEFT);
        }

        return  $driver_number;


    }

         private function parseExcelDate($value)
        {
            if (!isset($value) || trim((string) $value) === '') {
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

            $value = trim((string) $value);

            // Common date formats that show up depending on how the cell was entered/exported
            $formats = ['Y-m-d', 'Y-m-d H:i:s', 'd/m/Y', 'd-m-Y', 'm/d/Y', 'd.m.Y'];

            foreach ($formats as $format) {
                $parsed = \DateTime::createFromFormat('!' . $format, $value);
                $errors = \DateTime::getLastErrors();
                $hasErrors = $errors !== false && ($errors['warning_count'] > 0 || $errors['error_count'] > 0);

                if ($parsed && !$hasErrors) {
                    return Carbon::instance($parsed);
                }
            }

            // Last resort: let Carbon try to make sense of it
            try {
                return Carbon::parse($value);
            } catch (\Exception $e) {
                return null;
            }
        }

    private function parseGender($value)
    {
        $gender = ucfirst(strtolower(trim($value)));

        return in_array($gender, ['Male', 'Female']) ? $gender : null;
    }

     public function limit(): int
    {
        return 2500; // Import only the first 100 rows
    }

      public function updateLeaveDays($id){
        
        $employee = Employee::find($id);
        
        $leaveTypes = LeaveType::all();

       

            foreach ($leaveTypes as $leaveType) {

                $maximumDays = is_numeric($leaveType->entitlement)
                    ? (float) $leaveType->entitlement
                    : 0;

                $isAnnual = strtolower(trim($leaveType->name)) === 'annual';

                $accrualRate = $isAnnual
                    ? (
                        is_numeric($employee->accrual_rate)
                            ? (float) $employee->accrual_rate
                            : (float) ($leaveType->monthly_accrual_rate ?? 0)
                    )
                    : (float) ($leaveType->monthly_accrual_rate ?? 0);

                $approvedLeaveDaysTaken = Leave::where('employee_id', $employee->id)
                    ->where('leave_type_id', $leaveType->id)
                    ->where('hod_decision', 'approved')
                    ->where('management_decision', 'approved')
                    ->whereYear('from', date('Y'))
                    ->sum('days');

                $employeeLeave = EmployeeLeave::firstOrNew([
                    'employee_id' => $employee->id,
                    'leave_type_id' => $leaveType->id,
                ]);

                $employeeLeave->acrual_rate = $employeeLeave->acrual_rate ?? $accrualRate;
                $employeeLeave->maximum_leave_days = $employeeLeave->maximum_leave_days ?? $maximumDays;

                if (strtolower(trim($leaveType->name)) === 'annual') {

                    $employeeLeave->available_leave_days = is_numeric($employee->leave_days)
                        ? (float) $employee->leave_days
                        : max(0, $maximumDays - $approvedLeaveDaysTaken);

                } else {

                    $employeeLeave->available_leave_days = max(
                        0,
                        $maximumDays - $approvedLeaveDaysTaken
                    );
                }

                $employeeLeave->save();
            }
  

    }
   

    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function collection(Collection $rows)
    {
      foreach ($rows as $row) {
        // Skip empty rows
        if ($row->filter()->isEmpty()) {
            continue;
        }

        $name    = $row->get('name');
        $surname = $row->get('surname');

        if (!$name || !$surname) {
            // required for matching
            continue;
        }

        // Common columns
        $email            = $row->get('email');
        $gender           = $row->get('gender');
        $dob              = $row->get('dob');
        $phone            = $row->get('phonenumber');
        $idNumber         = $row->get('idnumber');
        $country          = $row->get('country');
        $city             = $row->get('city');
        $suburb           = $row->get('suburb');
        $contractDuration = $row->get('contract_duration');
        $startDate        = $row->get('start_date');
        $expiryDate       = $row->get('expiry_date');
        $streetAddress    = $row->get('streetaddress');
        $nextOfKin        = $row->get('nextofkin');
        $relationship     = $row->get('relationship');
        $contact          = $row->get('contact');
        $departments      = $row->get('departments');
        $post             = $row->get('post');

        // Driver-specific columns
        $transporterNumber  = $row->get('transporter_number');
        $licenseNumber      = $row->get('license_number');
        $passportNumber     = $row->get('passport_number');
        $experience         = $row->get('experience');
        $licenseClass       = $row->get('class');
        $reference          = $row->get('reference');
        $referencePhone     = $row->get('reference_phonenumber');

        $existingEmployeeUser = User::where('name', $name)
        ->where('surname', $surname)
        ->where('category', 'employee')
        ->first();

        if ($existingEmployeeUser) {
            Log::warning('Skipped row: Person already registered as an employee.', $row->toArray());
            continue;
        }

        DB::transaction(function () use (
            $row,
            $name,
            $surname,
            $email,
            $gender,
            $dob,
            $phone,
            $idNumber,
            $country,
            $city,
            $suburb,
            $contractDuration,
            $startDate,
            $expiryDate,
            $streetAddress,
            $nextOfKin,
            $relationship,
            $contact,
            $departments,
            $post,
            $transporterNumber,
            $licenseNumber,
            $passportNumber,
            $experience,
            $licenseClass,
            $reference,
            $referencePhone
        ) {

            $pin = $this->generatePIN();

            /*
            |----------------------
            | TRANSPORTER (optional)
            |----------------------
            */
            $transporter = $transporterNumber
                ? Transporter::where('transporter_number', $transporterNumber)->first()
                : null;

            $transporterId = optional($transporter)->id;

            /*
            |----------------------
            | USER (by name + surname)
            |----------------------
            */
        

            $user = User::firstOrNew([
                'name'    => $name,
                'surname' => $surname,
                'category' => 'driver',
            ]);

            $existingUser = $user->exists;

            $user->category = 'driver'; // you can change to 'employee' if you really want old behaviour
            $user->is_admin = '0';
            $user->active   = '1';
            $user->email    = $email;

            if (!$existingUser) {
                $user->password = Hash::make($pin);
            }

            $user->save();
            $user->roles()->syncWithoutDetaching([3]);

            /*
            |----------------------
            | EMPLOYEE (by name + surname)
            |----------------------
            */
            $employee = Employee::firstOrNew([
                'name'    => $name,
                'surname' => $surname,
            ]);

            $existingEmployee = $employee->exists;

            if (!$existingEmployee) {
                $employee->company_id      = optional(Auth::user()->employee)->company_id;
                $employee->creator_id      = Auth::id();
                $employee->user_id         = $user->id;
                $employee->employee_number = $this->employeeNumber();
                $employee->pin             = $pin;
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
            $employee->duration      = is_numeric($contractDuration) ? (int) $contractDuration : null;
            $employee->start_date    = $this->parseExcelDate($startDate);
            $employee->expiration    = $this->parseExcelDate($expiryDate);
            $employee->street_address = $streetAddress;
            $employee->next_of_kin   = $nextOfKin;
            $employee->relationship  = $relationship;
            $employee->contact       = $contact;

            $postTitle = trim((string) $post);
            if ($postTitle !== '') {
                $jobTitle = JobTitle::firstOrCreate(
                    ['title' => $postTitle],
                    ['user_id' => Auth::id()]
                );
                $employee->post = $jobTitle->title;
            }

            $employee->save();
            $employee->ranks()->syncWithoutDetaching([3]);

            if (!empty($departments)) {
                $departmentNames = array_filter(array_map('trim', explode(',', $departments)));

                if (!empty($departmentNames)) {
                    $departmentIds = Department::whereIn('name', $departmentNames)->pluck('id')->toArray();
                    $employee->departments()->sync($departmentIds);
                }
            }

            $this->updateLeaveDays($employee->id);

            if ($this->send_creds == True) {
                if (!empty($employee->email) && filter_var($employee->email, FILTER_VALIDATE_EMAIL)) {
                    Mail::to($employee->email)->send(new AccountCreationMail($user, $this->company,$pin));
                }
              }

            /*
            |----------------------
            | DRIVER (by employee + license_number)
            |----------------------
            */
            $driver = null;
            $existingDriver = false;

           if ($employee->id && $licenseNumber) {
                $driver = Driver::firstOrNew([
                    'employee_id'    => $employee->id,
                    'license_number' => $licenseNumber,
                ]);
                $existingDriver = $driver->exists;
            } elseif ($employee->id) {
                // fallback: at least enforce one driver per employee
                $driver = Driver::firstOrNew([
                    'employee_id' => $employee->id,
                ]);
                $existingDriver = $driver->exists;
            } else {
                // truly broken row – probably better to skip
                Log::warning('Skipped row: employee missing when creating driver', $row->toArray());
                return;
            }

            if (!$existingDriver) {
                $driver->company_id   = optional(Auth::user()->employee)->company_id;
                $driver->creator_id   = Auth::id();
                $driver->user_id      = $user->id;
                $driver->employee_id  = $employee->id;
                $driver->driver_number = $this->driverNumber();
            }

            if ($transporterId) {
                $driver->transporter_id = $transporterId;
            }

            $driver->license_number        = $licenseNumber;
            $driver->passport_number       = $passportNumber;
            $driver->experience            = $experience;
            $driver->class                 = $licenseClass;
            $driver->reference             = $reference;
            $driver->reference_phonenumber = $referencePhone;

            $driver->save();

            /*
            |----------------------
            | EMPLOYEE POSITION
            | Only when user + employee + driver already existed before import
            | (mirrors your original "isset($user) && isset($employee) && isset($driver)" path)
            |----------------------
            */
            if ($existingUser && $existingEmployee && $existingDriver) {
                $jobTitleId  = JobTitle::where('title', $employee->post)->first()?->id ?? null;
                $rankId      = $employee->ranks->first()?->id ?? null;
                $branchId    = $employee->branch_id ?? null;
                $departmentId = $employee->departments->first()?->id ?? null;

                // Check if an active position with same attributes already exists
                $positionExists = EmployeePosition::where('employee_id', $employee->id)
                    ->where('job_title_id', $jobTitleId)
                    ->where('rank_id', $rankId)
                    ->where('branch_id', $branchId)
                    ->where('department_id', $departmentId)
                    ->whereNull('end_date') // if you have end_date / active flag
                    ->exists();

                if (! $positionExists) {
                    $employeePosition = new EmployeePosition;
                    $employeePosition->employee_id   = $employee->id;
                    $employeePosition->job_title_id  = $jobTitleId;
                    $employeePosition->rank_id       = $rankId;
                    $employeePosition->branch_id     = $branchId;
                    $employeePosition->department_id = $departmentId;
                    $employeePosition->grade_id      = $employee->grade_id ?? null;
                    $employeePosition->start_date    = $employee->start_date ?? null;
                    $employeePosition->changed_by    = Auth::id();
                    $employeePosition->change_reason = 'Appointment';
                    $employeePosition->remarks       = 'Initial Appointment';
                    $employeePosition->save();
                }
            }
        });
    }
       
    }

    public function rules(): array{
        return[
            // '*.transporter_number' => ['required'],
            // '*.license_number' => ['nullable','unique:drivers,license_number,NULL,id,deleted_at,NULL'],
            // '*.passport_number' => ['nullable','unique:drivers,passport_number,NULL,id,deleted_at,NULL'],
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
