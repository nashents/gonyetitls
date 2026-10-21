<?php

namespace App\Imports;

use App\Imports\Concerns\AssignsRolesAndRanks;
use App\Imports\Concerns\FillsOnlyFilledCells;
use App\Imports\Concerns\SkipsTemplateExampleRows;

use App\Mail\AccountCreationMail;
use App\Models\Count;
use App\Models\Department;
use App\Models\Employee;
use App\Models\EmployeeLeave;
use App\Models\EmployeePosition;
use App\Models\JobTitle;
use App\Models\Leave;
use App\Models\LeaveType;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
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
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithValidation;

class EmployeesImport implements  ToCollection,SkipsEmptyRows, WithLimit,
WithHeadingRow,
SkipsOnError,
WithValidation,
WithChunkReading,
WithCalculatedFormulas,
WithBatchInserts,
WithMultipleSheets
{
    use SkipsTemplateExampleRows;
    use AssignsRolesAndRanks;
    use FillsOnlyFilledCells;

    // Only the first sheet holds data; the template's Instructions/Lists sheets are ignored.
    public function sheets(): array
    {
        return [0 => $this];
    }

    use Importable, SkipsErrors;

    public $company;
    public $send_creds;

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

    public function __construct($send_creds)
    {
        $this->send_creds = $send_creds;
        if (Auth::user()->company) {
            $this->company = Auth::user()->company;
        } elseif (optional(Auth::user()->employee)->company) {
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
        $gender = ucfirst(strtolower(trim((string) $value)));

        return in_array($gender, ['Male', 'Female']) ? $gender : null;
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
                $employeeLeave->maximum_leave_days = $employeeLeave->maximum_leave_days ?? ($leaveType->maximum_leave_days ?? 0);

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
                
                $middlename          = $row->get('middlename');
                $email          = $row->get('email');
                $gender         = $row->get('gender');
                $dob            = $row->get('dob');
                $phone          = $row->get('phonenumber');
                $idNumber       = $row->get('idnumber');
                $country        = $row->get('country');
                $city           = $row->get('city');
                $departments           = $row->get('departments');
                $post           = $row->get('post');
                $suburb         = $row->get('suburb');
                $contract       = $row->get('contract_duration');
                $startDate      = $row->get('start_date');
                $expiryDate     = $row->get('expiry_date');
                $streetAddress  = $row->get('streetaddress');
                $nextOfKin      = $row->get('nextofkin');
                $relationship   = $row->get('relationship');
                $contact        = $row->get('contact');
                $roles          = $row->get('roles');
                $rank           = $row->get('rank');
                $who            = trim("$name $surname");

                DB::transaction(function () use ($row,$name,$surname,$middlename,$email,$gender,$dob,$phone,$idNumber,
                    $country,$city,$suburb,$contract,$startDate,$expiryDate,$streetAddress,$nextOfKin,$relationship,$contact,
                    $departments,$post,$roles,$rank,$who
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
                    $this->fillFilled($user, ['email' => $email, 'phonenumber' => $phone]);

                    // Username defaults to the email, else the phone number; existing logins keep theirs.
                    if ($isNewUser || blank($user->username)) {
                        $hasEmail = filled($email);
                        $user->use_email_as_username = $hasEmail ? 1 : 0;
                        $user->username = $hasEmail ? trim($email) : (filled($phone) ? trim($phone) : null);
                    }

                    if ($isNewUser) {
                        $user->password = Hash::make($pin);
                    }

                    $user->save();

                    $this->assignRoles($user, $roles, $who);

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
                    // Blank cells keep what is already on the record.
                    $this->fillFilled($employee, [
                        'middle_name'    => $middlename,
                        'gender'         => $this->parseGender($gender),
                        'dob'            => $this->parseExcelDate($dob),
                        'email'          => $email,
                        'phonenumber'    => $phone,
                        'idnumber'       => $idNumber,
                        'country'        => $country,
                        'city'           => $city,
                        'suburb'         => $suburb,
                        'duration'       => is_numeric($contract) ? (int) $contract : null,
                        'start_date'     => $this->parseExcelDate($startDate),
                        'expiration'     => $this->parseExcelDate($expiryDate),
                        'street_address' => $streetAddress,
                        'next_of_kin'    => $nextOfKin,
                        'relationship'   => $relationship,
                        'contact'        => $contact,
                    ]);

                    $postTitle = trim((string) $post);
                    if ($postTitle !== '') {
                        $jobTitle = JobTitle::firstOrCreate(
                            ['title' => $postTitle],
                            ['user_id' => Auth::id()]
                        );
                        $employee->post = $jobTitle->title;
                    }

                    $employee->save();
                   
                   

                    $this->assignRanks($employee, $rank, $who);

                    if (!empty($departments)) {
                        $departmentNames = array_filter(array_map('trim', explode(',', $departments)));

                        if (!empty($departmentNames)) {
                            $departmentIds = collect($departmentNames)->map(function ($departmentName) {
                                return Department::firstOrCreate(
                                    ['name' => $departmentName],
                                    ['user_id' => Auth::id()]
                                )->id;
                            })->toArray();

                            $employee->departments()->sync($departmentIds);
                            $employee->ensureDefaultDepartment();
                        }
                    }

                    $this->updateLeaveDays($employee->id);

                    /*
                    |----------------------
                    | EMPLOYEE POSITION
                    | Only when both user & employee existed already,
                    | same behaviour as your original code
                    |----------------------
                    */
                    $jobTitleId   = JobTitle::where('title', $employee->post)->first()?->id ?? null;
                    $rankId       = $employee->ranks->first()?->id ?? null;
                    $branchId     = $employee->branch_id ?? null;
                    $departmentId = $employee->departments->first()?->id ?? null;

                    // Re-uploading the same file must not stack duplicate position rows.
                    $positionExists = EmployeePosition::where('employee_id', $employee->id)
                        ->where('job_title_id', $jobTitleId)
                        ->where('rank_id', $rankId)
                        ->where('branch_id', $branchId)
                        ->where('department_id', $departmentId)
                        ->whereNull('end_date')
                        ->exists();

                    if ($existingPair && !$positionExists) {
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

                     // Only new logins get a password from $pin, so only they are sent one.
                     if ($this->send_creds == True && $isNewUser) {
                        if (!empty($employee->email) && filter_var($employee->email, FILTER_VALIDATE_EMAIL)) {
                            Mail::to($employee->email)->send(new AccountCreationMail($user, $this->company,$pin));
                        }
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
