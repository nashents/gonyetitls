<?php

namespace App\Http\Livewire\Employees;

use Carbon\Carbon;
use App\Models\Rank;
use App\Models\Role;
use App\Models\User;
use App\Models\Count;
use App\Models\Grade;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Country;
use Livewire\Component;
use App\Models\Currency;
use App\Models\Document;
use App\Models\Employee;
use App\Models\JobTitle;
use App\Models\Province;
use App\Models\Department;
use App\Models\BankAccount;
use Livewire\WithFileUploads;
use App\Models\DepartmentHead;
use App\Models\EmployeePosition;
use App\Mail\AccountCreationMail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;

class Create extends Component
{
    use WithFileUploads;

    public $departments;
    public $branches;
    public $countries;
    public $selectedCountry;
    public $provinces;
    public $province_id;
    public $employee_id;
    public $name;
    public $middle_name;
    public $surname;
    public $gender;
    public $dob;
    public $address;
    public $post;
    public $salary;
    public $frequency;
    public $duration;
    public $currencies;
    public $currency_id;
    public $expiration;
    public $expiry_date;
    public $next_of_kin;
    public $relationship;
    public $contact;
    public $grade_id;
    public $grades;
    
    //bank vars
    public $bank_name;
    public $bank_branch;
    public $account_name;
    public $account_number;
    public $bank_currency_id;
    public $branch_code;
    public $swift_code;


    public $custom_ref;
    public $companies;
    public $company_id;

    public $branch_id;
    public $role_id;
    public $roles;
    public $ranks;
    public $rank_id;
    public $email;
    public $personal_email;
    public $idnumber;
    public $phonenumber;
    public $city;
    public $suburb;
    public $street_address;
    public $start_date;
    public $end_date;
    public $leave_days;
    public $accrual_rate;

    public $selectedRank = Null;
    public $selectedDepartment = [];
    public $job_title;
    public $job_titles;
    public $title;
    public $file;
    public $use_email_as_username;

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
        $this->departments = Department::orderBy('name','asc')->get();
        $this->branches = Branch::orderBy('name','asc')->get();
        $this->grades = Grade::orderBy('grade_code','asc')->get();
        $this->job_titles = JobTitle::orderBy('title','asc')->get();
        $this->countries = Country::orderBy('name','asc')->get();
        $this->roles = Role::latest()->get();
        $this->currencies = Currency::orderBy('name','asc')->get();
        $this->companies = Company::where('type','!=','admin')->orderBy('name','asc')->get();
        $this->ranks = Rank::latest()->get();
        $this->provinces = collect();
        $this->use_email_as_username = 1;
      }

      public function updatedSelectedCountry($id){
        if (!is_null($id)) {
          $this->provinces = Province::where('country_id',$id)->orderBy('name','asc')->get();
        }
      }

    
      public function updated($value){
          $this->validateOnly($value);
      }

      protected $messages =[
        'selectedDepartment.required' => 'Select Department',
        'branch_id.nullable' => 'Select Branch',
        'role_id.required' => 'Select Role',
        'company_id.required' => 'Select Company',
      ];

      protected $rules = [
          'name' => 'required|alpha|min:2',
          'middle_name' => 'nullable|alpha|min:2',
          'surname' => 'required|alpha|min:2',
          'dob' => 'nullable',
          'email' => 'nullable|email|unique:users,email,NULL,id,deleted_at,NULL',
          'personal_email' => 'nullable|email|unique:employees,personal_email,NULL,id,deleted_at,NULL',
          'phonenumber' => 'nullable|unique:employees,phonenumber,NULL,id,deleted_at,NULL|max:13',
          'company_id' => 'required',
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
    public function updatedCompanyId($id){
        $this->company_id = $id;
    }

      public function refresh($category){

        if($category == "job_titles"){
            $this->job_titles = JobTitle::orderBy('title','asc')->get();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Jop Titles Refreshed Successfully!!."
            ]);
        }
        elseif($category == "grades"){
            $this->grades = Grade::orderBy('grade_name','asc')->get();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Grades Refreshed Successfully!!."
            ]);
        }
        elseif($category == "branches"){
            $this->branches = Branch::orderBy('name','asc')->get();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Branches Refreshed Successfully!!."
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

    public function updatedSelectedDepartment($department)
    {
        if (!is_null($department) ) {
        $this->job_titles = JobTitle::where('department_id', $department)->get();
        }
    }
      public function store(){

        DB::transaction(function () {

          $pin = $this->generatePIN();
          $user = new User;
          $user->name = $this->name;
          $user->surname = $this->surname;
          $user->category = 'employee';
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

        if (isset($this->email) && filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
          Mail::to($this->email)->send(new AccountCreationMail($user, $company,$pin));
        }
       

          $employee = new Employee;
          $employee->company_id = $this->company_id;
          $employee->creator_id = Auth::user()->id;
          $employee->user_id = $user->id;
          $employee->employee_number = $this->employeeNumber();
          $employee->name = $this->name;
          $employee->middle_name = $this->middle_name;
          $employee->surname = $this->surname;
          $employee->custom_ref = $this->custom_ref;
          $employee->phonenumber = $this->phonenumber;
          $employee->email = $this->email;
          $employee->grade_id = $this->grade_id;
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
          $employee->street_address = $this->street_address;
          $employee->idnumber = $this->idnumber;
          $employee->post = $this->job_title;
          $employee->duration = $this->duration;
          $employee->frequency = $this->frequency;
          $employee->currency_id = $this->currency_id;
          $employee->salary = $this->salary;
          $employee->expiration = $this->expiration; 
          $employee->next_of_kin = $this->next_of_kin;
          $employee->relationship = $this->relationship;
          $employee->contact = $this->contact;

          $employee->start_date =  $this->start_date;
          $employee->end_date =  $this->end_date;

          $employee->leave_days = $this->leave_days;
          $employee->accrual_rate = $this->accrual_rate;
          $employee->branch_id = $this->branch_id;
          $employee->save();
          
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
        
          Session::flash('success','Employee Created Successfully!!');
          return redirect()->route('employees.index');

      });
      }
    public function render()
    {      
      $this->departments = Department::orderBy('name','asc')->get();
      $this->branches = Branch::orderBy('name','asc')->get();
      $this->job_titles = JobTitle::orderBy('title','asc')->get();
      $this->countries = Country::orderBy('name','asc')->get();
        return view('livewire.employees.create',[
          'departments' => $this->departments,
          'branches' => $this->branches,
          'job_titles' => $this->job_titles,
          'countries' => $this->countries,
        ]);
    }
}
