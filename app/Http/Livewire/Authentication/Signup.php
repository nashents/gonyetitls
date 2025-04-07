<?php

namespace App\Http\Livewire\Authentication;

use App\Models\Rank;
use App\Models\Role;
use App\Models\User;
use App\Models\Admin;
use App\Models\Count;
use App\Models\Branch;
use App\Models\Company;
use Livewire\Component;
use App\Models\Employee;
use App\Models\Department;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class Signup extends Component
{
    public $name;
    public $surname;
    public $phonenumber;
    public $idnumber;
    public $branch_id;
    public $department_id;
    public $role_id;
    public $company_id;
    public $companies;
    public $roles;
    public $rank_id;
    public $ranks;
    public $departments;
    public $branches;
    public $email;
    public $password;
    public $password_confirmation;
    public $employee_id;
    public $gender;
    public $dob;
    public $post;
    public $country;
    public $city;
    public $province;
    public $suburb;
    public $street_address;
    public $start_date;
    public $leave_days = "90";

    public function mount(){
        $this->roles = Role::all();
        $this->ranks = Rank::all();
        $this->departments = Department::all();
        $this->branches = Branch::all();
        $this->companies = Company::where('name','Gonyeti TLS')->get();
    }
    public function updated($value){
        $this->validateOnly($value);
    }
    protected $rules =[
        'name' => 'required|alpha|min:2',
        'surname' => 'required|alpha|min:2',
        'gender' => 'required',
        'dob' => 'required|date',
        'email' => 'required|email|unique:users',
        'phonenumber' => 'required|digits:10',
        'idnumber' => 'required|string|max:12',
        'country' => 'required|string',
        'city' => 'required',
        'province' => 'required',
        'suburb' => 'required',
        'street_address' => 'required',
        'start_date' => 'required',
        'leave_days' => 'required',
        'post' => 'required',
        'branch_id' => 'required',
        'department_id' => 'required',
        'password' => 'required|min:2|confirmed',

    ];
    protected $messages =[

        'role_id.*.required' => 'Select Role',
        'department_id.*.required' => 'Select Department',
        'branch_id.*.required' => 'Select Branch',

    ];
    public function employeeNumber(){

        $str = Company::find($this->company_id)->name;
        $words = explode(' ', $str);
        if (isset($words[1][0])) {
            $initials = $words[0][0].$words[1][0];
        }else {
            $initials = $words[0][0];
        }

            $employee = Employee::orderBy('id','desc')->first();

        if (!$employee) {
            $employee_number =  $initials .'E'. str_pad(1, 5, "0", STR_PAD_LEFT);
        }else {
            $number = $employee->id + 1;
            $employee_number =  $initials .'E'. str_pad($number, 5, "0", STR_PAD_LEFT);
        }

        return  $employee_number;


    }
    public function store(){

            $user = new User;
            $user->name = $this->name;
            $user->surname = $this->surname;
            $user->category = 'admin';
            $user->is_admin = 1;
            $user->email = $this->email;
            $user->password = bcrypt($this->password);
            $user->save();
            $user->roles()->sync($this->role_id);
            Auth::login($user);

            $admin = new Admin;
            $admin->user_id = $user->id;
            $admin->name = $this->name;
            $admin->surname = $this->surname;
            $admin->phonenumber = $this->phonenumber;
            $admin->email = $this->email;
            $admin->gender = $this->gender;
            $admin->dob = $this->dob;
            $admin->country = $this->country;
            $admin->city = $this->city;
            $admin->street_address = $this->street_address;
            $admin->suburb = $this->suburb;
            $admin->designation = $this->post;

            $employee = new Employee;
            $employee->company_id = $this->company_id;
            $employee->user_id = $user->id;
            $employee->employee_number = $this->employeeNumber();
            $employee->name = $this->name;
            $employee->surname = $this->surname;
            $employee->phonenumber = $this->phonenumber;
            $employee->email = $this->email;
            $employee->idnumber = $this->idnumber;
            $employee->gender = $this->gender;
            $employee->dob = $this->dob;
            $employee->country = $this->country;
            $employee->province = $this->province;
            $employee->city = $this->city;
            $employee->street_address = $this->street_address;
            $employee->suburb = $this->suburb;
            $employee->post = $this->post;
            $employee->start_date = $this->start_date;
            $employee->leave_days = $this->leave_days;
            $employee->branch_id = $this->branch_id;
            $employee->save();
            $employee->departments()->sync($this->department_id);
            $employee->ranks()->sync($this->rank_id);

            if(Auth::user()->is_admin()){
                Session::flash('success','Welcome to your Admin Dashboard');
                return redirect(route('dashboard.index'));
            }else{
                Session::flash('error','Not authorised to login');
                return redirect()->back();
            }

    }

    public function render()
    {
        return view('livewire.authentication.signup');
    }
}
