<?php

namespace App\Http\Livewire\Drivers;

use App\Mail\AccountCreationMail;
use App\Models\BankAccount;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Count;
use App\Models\Country;
use App\Models\Currency;
use App\Models\Department;
use App\Models\DepartmentHead;
use App\Models\Document;
use App\Models\Driver;
use App\Models\Employee;
use App\Models\EmployeeLeave;
use App\Models\EmployeePosition;
use App\Models\Grade;
use App\Models\JobTitle;
use App\Models\Leave;
use App\Models\LeaveType;
use App\Models\Province;
use App\Models\Rank;
use App\Models\Role;
use App\Models\Transporter;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use Livewire\Component;
use Livewire\WithFileUploads;

class Create extends Component
{
    use WithFileUploads;


    public $name;
    public $middle_name;
    public $surname;
    public $gender;
    public $dob;
    public $idnumber;
    public $passport_number;
    public $phonenumber;

    public $experience;
    public $license_number;
    public $countries;
    public $selectedCountry;
    public $provinces;
    public $province_id;

    public $transporters;
    public $transporter_id;
    public $companies;
    public $company_id;
    public $class;
    public $email;
    public $personal_email;
    public $post;
    public $address;
    public $department_id;
    public $departments;
    public $branch_id;
    public $branches;
    public $currencies;
    public $currency_id;
    public $salary;
    public $frequency;
    public $role_id;
    public $roles;
    public $ranks;
    public $rank_id;
    public $start_date;
    public $end_date;
    public $leave_days;
    public $accrual_rate;
    public $duration;
    public $expiration;
    public $expiry_date;
    public $next_of_kin;
    public $relationship;
    public $contact;

    //bank vars
    public $bank_name;
    public $bank_branch;
    public $account_name;
    public $account_number;
    public $bank_currency_id;
    public $branch_code;
    public $swift_code;
    public $custom_ref;

    public $city;
    public $suburb;
    public $street_address;
    public $use_email_as_username;

    public $reference;
    public $reference_phonenumber;

    public $selectedDepartment = Null;
    public $job_title;
    public $job_titles;
    public $employee_id;
     public $grade_id;
    public $grades;

    public $title;
    public $file;
    public $expires_at;

    public $inputs = [];
    public $i = 1;
    public $n = 1;

    public function add($i)
    {
        $i = $i + 1;
        $this->i = $i;
        array_push($this->inputs ,$i);
    }

    public function remove($i)
    {
        unset($this->inputs[$i]);
    }

    public function mount(){
        $this->companies = Company::where('type','!=','admin')->orderBy('name','asc')->get();
        $this->grades = Grade::orderBy('grade_code','asc')->get();
        $this->departments = Department::orderBy('name','asc')->get();
        $this->transporters = Transporter::orderBy('name','asc')->get();
        $this->branches = Branch::orderBy('name','asc')->get();
        $this->roles = Role::orderBy('name','asc')->get();
        $this->currencies = Currency::latest()->get();
        $this->ranks = Rank::orderBy('name','asc')->get();
        $this->job_titles = JobTitle::orderBy('title','asc')->get();
        $this->use_email_as_username = 1;
        $this->countries = Country::orderBy('name','asc')->get();
        $this->provinces = collect();
        
      }


      public function updated($value){
          $this->validateOnly($value);
      }
      protected $messages =[
        'department_id.required' => 'Select Department',
        'branch_id.nullable' => 'Select Branch',
        'role_id.required' => 'Select Role',
        'transporter_id.required' => 'Select Transporter',
      ];

      protected $rules = [
          'name' => 'required',
          'middle_name' => 'nullable',
          'surname' => 'required',
          'dob' => 'nullable',
          'email' => 'nullable|email|unique:users,email,NULL,id,deleted_at,NULL',
          'personal_email' => 'nullable|email|unique:employees,personal_email,NULL,id,deleted_at,NULL',
          'phonenumber' => 'nullable|unique:employees,phonenumber,NULL,id,deleted_at,NULL|max:13',
          'transporter_id' => 'required',
          'department_id' => 'required',
          'file.0' => 'nullable|file|mimes:docx,doc,pdf,xls,xlsx,pptx|max:10000',
          'file.*' => 'required|file|mimes:docx,doc,pdf,xls,xlsx,pptx|max:10000',
      ];


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


    

        public function refresh($category){

        if($category == "job_titles"){
            $this->job_titles = JobTitle::orderBy('title','asc')->get();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Jop Titles Refreshed Successfully!!."
            ]);
        }
        elseif($category == "branches"){
            $this->branches = Branch::orderBy('name','asc')->get();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Branches Refreshed Successfully!!."
            ]);
        }
         elseif($category == "grades"){
            $this->grades = Grade::orderBy('grade_name','asc')->get();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Grades Refreshed Successfully!!."
            ]);
        }
        elseif($category == "countries"){
            $this->countries = Country::orderBy('name','asc')->get();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Countries Refreshed Successfully!!."
            ]);
        }
        elseif($category == "provinces"){
            $this->provinces = Province::orderBy('name','asc')->get();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Provinces Refreshed Successfully!!."
            ]);
        }
      }


    public function updatedTransporterId($id){
        $this->transporter_id = $id;
    }
    public function updatedCompanyId($id){
        $this->company_id = $id;
    }

    public function employeeNumber(){

        if ($this->company_id) {
            $company = Company::find($this->company_id);
            $str = $company?->name;
            $words = explode(' ', $str);
            if (isset($words[1][0])) {
                $initials = $words[0][0].$words[1][0];
            }else {
                $initials = $words[0][0];
            }

        }else{
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


      if ($this->transporter_id) {
        
            $transporter = Transporter::find($this->transporter_id);
            $str = $transporter?->name;
            $words = explode(' ', $str);
            if (isset($words[1][0])) {
                $initials = $words[0][0].$words[1][0];
            }else {
                $initials = $words[0][0];
            }

        }else{
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

    public function updatedSelectedCountry($id){
        if (!is_null($id)) {
          $this->provinces = Province::where('country_id',$id)->orderBy('name','asc')->get();
        }
      }
      
    public function updatedSelectedDepartment($department)
    {
        if (!is_null($department) ) {
        $this->job_titles = JobTitle::where('department_id', $department)->get();
        }
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

    public function store(){

      $driver = null;
      DB::transaction(function () use (&$driver) {

      $pin = $this->generatePIN();

      $user = new User;
      $user->name = $this->name;
      $user->surname = $this->surname;
      $user->category = 'driver';
      $user->email = $this->email;
      $user->phonenumber = $this->phonenumber;
        $user->use_email_as_username = $this->use_email_as_username;
        if ($this->use_email_as_username == TRUE) {
        $user->username = $this->email;
        }else{
        $user->username = $this->phonenumber;
        }
      $user->password = Hash::make($pin);
      $user->save();
      $user->roles()->sync($this->role_id);


      if (isset(Auth::user()->company)) {
        $company = Auth::user()->company;
    }elseif (isset(Auth::user()->employee->company)) {
        $company = Auth::user()->employee->company;
    }

    Mail::to($this->email)->send(new AccountCreationMail($user, $company,$pin));

      $employee = new Employee;
      $employee->company_id = $this->company_id;
      $employee->creator_id = Auth::user()->id;
      $employee->user_id = $user->id;
      $employee->employee_number = $this->employeeNumber();
      $employee->custom_ref = $this->custom_ref;
      $employee->name = $this->name;
      $employee->middle_name = $this->middle_name;
      $employee->surname = $this->surname;
      $employee->phonenumber = $this->phonenumber;
      $employee->email = $this->email;
      $employee->personal_email = $this->personal_email;
      $employee->pin = $pin;
      $employee->gender = $this->gender;
      $employee->dob = $this->dob;
      $country = Country::find($this->selectedCountry);
          if (isset($country)) {
            $employee->country = $country->name;
          }
          $province = Province::find($this->province_id);
          if (isset($province)) {
            $employee->province = $province->name;
          }
      $employee->city = $this->city;
      $employee->suburb = $this->suburb;
      $employee->salary = $this->salary;
      $employee->frequency = $this->frequency;
      $employee->currency_id = $this->currency_id;
      $employee->street_address = $this->street_address;
      $employee->idnumber = $this->idnumber;
      $employee->grade_id = $this->grade_id;
      $employee->post = $this->job_title;
      $employee->duration = $this->duration;
      $employee->expiration = $this->expiration;
      $employee->next_of_kin = $this->next_of_kin;
      $employee->relationship = $this->relationship;
      $employee->contact = $this->contact;
      $employee->start_date = $this->start_date;
      $employee->end_date = $this->end_date;
      $employee->leave_days = $this->leave_days;
      $employee->accrual_rate = $this->accrual_rate;
      $employee->branch_id = $this->branch_id;
      $employee->save();

      $this->updateLeaveDays($employee->id);

      $employee->departments()->sync($this->selectedDepartment);
      $employee->ranks()->sync($this->rank_id);

        $employee_position  = new EmployeePosition;
        $employee_position->employee_id = $employee->id;
        $employee_position->job_title_id = JobTitle::where('title',$employee->post)->first()?->id ?? Null;
        $employee_position->rank_id = $employee->ranks->first()?->id ?? Null;
        $employee_position->branch_id = $employee->branch_id ?? Null;
        $employee_position->department_id = $employee->departments->first()?->id ?? Null;
        $employee_position->grade_id = $employee->grade_id ?? Null;
        $employee_position->start_date = $employee->start_date ?? Null;
        $employee_position->changed_by = Auth::user()->id;
        $employee_position->change_reason = "Appointment";
        $employee_position->remarks = "Initial Appointment";
        $employee_position->save();

       if ($this->account_number && $this->bank_name) {
              $bank_account = new BankAccount;
              $bank_account->user_id =  Auth::user()->id;
              $bank_account->employee_id =  $employee->id;
              $bank_account->name =  $this->bank_name;
              $bank_account->account_name =  $this->account_name;
              $bank_account->account_number = $this->account_number;
              $bank_account->branch = $this->bank_branch;
              $bank_account->currency_id = $this->bank_currency_id;
              $bank_account->branch_code = $this->branch_code;
              $bank_account->swift_code= $this->swift_code;
              $bank_account->save();
          }

      $rank = Rank::find($this->rank_id);
   
        $driver = new Driver;
        $driver->creator_id = Auth::user()->id;
        $driver->employee_id = $employee->id;
        $driver->user_id = $user->id;
        $driver->driver_number = $this->driverNumber();
        $driver->transporter_id = $this->transporter_id;
        $driver->license_number = $this->license_number;
        $driver->passport_number = $this->passport_number;
        $driver->class = $this->class;
        $driver->experience = $this->experience;
        $driver->reference = $this->reference;
        $driver->reference_phonenumber = $this->reference_phonenumber;
        $driver->status = 1;
        $driver->save();

        if (!empty($this->file) && isset($this->title)) {
            foreach ($this->file as $key => $value) {
              if(isset($this->file[$key])){
                  $file = $this->file[$key];
                  // get file with ext
                  $fileNameWithExt = $file->getClientOriginalName();
                  //get filename
                  $filename = pathinfo($fileNameWithExt, PATHINFO_FILENAME);
                  //get extention
                  $extention = $file->getClientOriginalExtension();
                  //file name to store
                  $fileNameToStore= $filename.'_'.time().'.'.$extention;
                  $file->storeAs('/documents', $fileNameToStore, 'my_files');

              }
              $document = new Document;
              $document->employee_id = $employee->id;
              $document->category = 'employee';
              if(isset($this->title[$key])){
              $document->title = $this->title[$key];
              }
              if (isset($fileNameToStore)) {
                  $document->filename = $fileNameToStore;
              }
              if(isset($this->expires_at[$key])){
                  $document->expires_at = Carbon::create($this->expires_at[$key])->toDateTimeString();
                  $today = now()->toDateTimeString();
                  $expire = Carbon::create($this->expires_at[$key])->toDateTimeString();
                  if ($today <=  $expire) {
                      $document->status = 1;
                  }else{
                      $document->status = 0;
                  }
              }else {
                $document->status = 1;
              }
              $document->save();

            }
                   # code...
        
        }
        
      Session::flash('success','Driver created successfully');
      return redirect()->route('drivers.index');

        });

      // Push the new driver to Sage as an Employee AFTER commit. Non-blocking:
      // a Sage outage/inactive integration never blocks driver creation.
      if ($driver) {
          try {
              app(\App\Services\Sage\SageSyncService::class)->syncDriver($driver);
          } catch (\Throwable $e) {
              \Illuminate\Support\Facades\Log::error('Driver Sage sync on create failed: ' . $e->getMessage());
          }
      }

      }
    public function render()
    {

        $this->departments = Department::orderBy('name','asc')->get();
        $this->branches = Branch::orderBy('name','asc')->get();
        $this->job_titles = JobTitle::orderBy('title','asc')->get();
        $this->countries = Country::orderBy('name','asc')->get();

        return view('livewire.drivers.create',[
            'departments' => $this->departments,
            'branches' => $this->branches,
            'job_titles' => $this->job_titles,
            'countries' => $this->countries,
        ]);
    }
}
