<?php

namespace App\Http\Livewire\Employees;

use Carbon\Carbon;
use App\Models\Rank;
use App\Models\Role;
use App\Models\User;
use App\Models\Count;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Country;
use Livewire\Component;
use App\Models\Currency;
use App\Models\Document;
use App\Models\Employee;
use App\Models\JobTitle;
use App\Models\Province;
use App\Models\Commission;
use App\Models\Department;
use App\Models\BankAccount;
use Livewire\WithFileUploads;
use App\Models\DepartmentHead;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;

class Edit extends Component
{

    use WithFileUploads;

    public $employee;
    public $employee_id;
    public $departments;
    public $countries;
    public $selectedCountry;
    public $provinces;
    public $province_id;
    public $branches;
    public $selected_departments;
    public $name;
    public $middle_name;
    public $surname;
    public $gender;
    public $ranks;
    public $rank_id ;
    public $dob;
    public $address;
    public $post;
    public $branch_id;
    public $role_id;
    public $roles;
    public $user_roles;
    public $personal_email;
    public $email;
    public $old_email;
    public $idnumber;
    public $phonenumber;
    public $city;
    public $suburb;
    public $street_address;
    public $start_date;
    public $end_date;
    public $user;
    public $user_id;
    public $employee_number;
    public $leave_days ;
    public $accrual_rate ;
    public $duration;
    public $salary;
    public $currencies;
    public $currency_id;
    public $frequency;
    public $expiration;
    public $next_of_kin;
    public $relationship;
    public $contact;
    public $use_email_as_username;
    public $grade;
    
    //bank vars
    public $bank_name;
    public $bank_branch;
    public $account_name;
    public $account_number;
    public $bank_currency_id;
    public $branch_code;
    public $swift_code;

    public $companies;
    public $company_id;

    public $title;
    public $file;
    public $selectedDepartment = [];
    public $job_title;
    public $job_titles;

    public $documents = [];
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

    public function mount($id){
        $this->employee_id = $id;
        $employee = Employee::find($id);
        $this->ranks = Rank::all();
        $this->documents = $employee->documents;
        foreach($this->documents as $key => $value){
            $this->title[$key] = $value->title;
        }
       foreach ($employee->departments as $department) {
        $this->selectedDepartment[] = $department->id;
       }

        foreach ($employee->user->roles as $role) {
            $this->role_id[] = $role->id;
        }
        foreach ($employee->ranks as $rank) {
            $this->rank_id[] = $rank->id;
        }
        $this->employee = $employee;
        $user = $employee->user;
        $this->user = $user;
        $this->user_id = $user->id;
        $this->currencies = Currency::orderBy('name','asc')->get();
        $this->departments = Department::orderBy('name','asc')->get();
        $this->branches = Branch::orderBy('name','asc')->get();
        $this->job_titles = JobTitle::latest()->get();
        $this->countries = Country::orderBy('name','asc')->get();
        $this->roles = Role::all();
        $this->companies = Company::where('type','!=','admin')->orderBy('name','asc')->get();
        $this->provinces = Province::orderBy('name','asc')->get();
        $this->job_title = $employee->post;
        $this->user_id = $employee->user_id;
        $this->employee_number = $employee->employee_number;
        $this->name = $employee->name ;
        $this->grade = $employee->grade ;

        if($employee->bank_account){
          $this->bank_name = $employee->bank_account->name;
          $this->bank_currency_id = $employee->bank_account->currency_id;
          $this->account_name = $employee->bank_account->account_name;
          $this->account_number = $employee->bank_account->account_number;
          $this->bank_branch = $employee->bank_account->bank_branch;
          $this->brach_code = $employee->bank_account->branch_code;
          $this->swift_code = $employee->bank_account->swift_code;
        }
      
        

        $this->middle_name = $employee->middle_name ;
        $this->company_id = $employee->company_id ;
        $this->surname = $employee->surname;
        $this->use_email_as_username = $employee->user ? $employee->user->use_email_as_username : "1";
        $this->phonenumber = $employee->phonenumber ;
        $this->email =   $employee->email ;
        $this->personal_email =   $employee->personal_email ;
        $this->old_email =   $employee->email ;
        $this->pin = $employee->pin;
        $this->gender = ucfirst($employee->gender);
        $this->frequency = $employee->frequency;
        $this->salary = $employee->salary;
        $this->currency_id = $employee->currency_id;
        $this->dob =  $employee->dob;

    
        $selected_country = $employee->country; 
        $country = Country::where('name',$selected_country)->first();
        if (isset($country)) {
            $this->selectedCountry = $country->id;
        }
        $selected_province =   $employee->province;
        $province = Province::where('name', $selected_province)->first();
        if (isset($province)) {
            $this->province_id = $province->id;
        }
        $this->city = $employee->city ;
       
        $this->suburb = $employee->suburb;
        $this->street_address = $employee->street_address;
        $this->idnumber = $employee->idnumber;
        $this->post = $employee->post;
        $this->start_date = $employee->start_date;
        $this->end_date = $employee->end_date;
        $this->duration = $employee->duration;
        $this->expiration = $employee->expiration;
        $this->next_of_kin = $employee->next_of_kin;
        $this->relationship = $employee->relationship;
        $this->contact = $employee->contact;
        $this->leave_days= $employee->leave_days;
        $this->accrual_rate= $employee->accrual_rate;
        $this->branch_id = $employee->branch_id;
        $this->selected_departments = $employee->departments;
      }

      public function updated($value){
          $this->validateOnly($value);
      }
      protected $messages =[
        'selectedDepartment.required' => 'Select Department',
        'branch_id.nullable' => 'Select Branch',
        'role_id.required' => 'Select Role',
      ];

      public function rules()
      {
        return [
            'name' => [
                'required',
                'alpha',
                'min:2',
            ],
            'middle_name' => [
                'nullable',
                'alpha',
                'min:2',
            ],
            'surname' => [
                'required',
                'alpha',
                'min:2',
            ],
            'dob' => [
                'nullable',
                'date',
            ],
            'email' => [
                'nullable',
                'email',
                Rule::unique('employees', 'email')
                    ->ignore(optional($this->employee)->id)
                    ->withoutTrashed(),
            ],
            'personal_email' => [
                'nullable',
                'email',
                Rule::unique('employees', 'personal_email')
                    ->ignore(optional($this->employee)->id)
                    ->whereNull('deleted_at'),
            ],
            'phonenumber' => [
                'nullable',
                'max:13',
                Rule::unique('employees', 'phonenumber')
                    ->ignore(optional($this->employee)->id)
                    ->whereNull('deleted_at'),
            ],
            'company_id' => [
                'required',
            ],
            'selectedDepartment' => [
                'required',
            ],
            'file.0' => [
                'nullable',
                'file',
                'mimes:docx,doc,pdf,xls,xlsx,pptx',
                'max:10000',
            ],
            'file.*' => [
                'required',
                'file',
                'mimes:docx,doc,pdf,xls,xlsx,pptx',
                'max:10000',
            ],
        ];
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

    public function updatedSelectedDepartment($department)
    {
        if (!is_null($department) ) {
        $this->job_titles = JobTitle::where('department_id', $department)->get();
        }
    }

  

      public function update(){

        $this->validate();
   
        DB::transaction(function () {
           
          $user = User::find($this->user_id);
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
          $user->update();
          $user->roles()->detach();
          $user->roles()->sync($this->role_id);

          $employee = Employee::find($this->employee_id);
          $employee->company_id = $this->company_id;
          $employee->name = $this->name;
          $employee->middle_name = $this->middle_name;
          $employee->surname = $this->surname;
          $employee->phonenumber = $this->phonenumber;
          $employee->email = $this->email;
          $employee->personal_email = $this->personal_email;
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
          $employee->grade = $this->grade;
          $employee->salary = $this->salary;
          $employee->frequency = $this->frequency;
          $employee->currency_id = $this->currency_id;
          $employee->expiration = $this->expiration; 
          $employee->next_of_kin = $this->next_of_kin;
          $employee->relationship = $this->relationship;
          $employee->contact = $this->contact;
          $employee->start_date =  $this->start_date;
          $employee->end_date =  $this->end_date;
          $employee->leave_days = $this->leave_days;
          $employee->accrual_rate = $this->accrual_rate;
          $employee->branch_id = $this->branch_id;
          
          $employee->update();
          $employee->departments()->detach();
          $employee->departments()->sync($this->selectedDepartment);
          $employee->ranks()->detach();
          $employee->ranks()->sync($this->rank_id);

          $bank_account = $employee->bank_account;
          if(isset($bank_account)){
            $bank_account->name =  $this->bank_name;
            $bank_account->account_name =  $this->account_name;
            $bank_account->account_number = $this->account_number;
            $bank_account->branch = $this->bank_branch;
            $bank_account->currency_id = $this->bank_currency_id;
            $bank_account->branch_code = $this->branch_code;
            $bank_account->swift_code= $this->swift_code;
            $bank_account->update();
          }else{
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
        
          Session::flash('success','Employee updated successfully');
          return redirect()->route('employees.index');

          });
      }

    public function render()
    {
        $this->departments = Department::orderBy('name','asc')->get();
        $this->branches = Branch::orderBy('name','asc')->get();
        $this->job_titles = JobTitle::latest()->get();
        $this->countries = Country::orderBy('name','asc')->get();
        $this->provinces = Province::orderBy('name','asc')->get();
        
        return view('livewire.employees.edit',[
            'departments' => $this->departments,
            'branches' => $this->branches,
            'job_titles' => $this->job_titles,
            'countries' => $this->countries,
            'provinces' => $this->provinces,
        ]);
    }
}
