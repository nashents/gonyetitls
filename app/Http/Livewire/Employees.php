<?php

namespace App\Http\Livewire;

use App\Models\Branch;
use Livewire\Component;
use App\Models\Employee;
use App\Models\Department;

class Employees extends Component
{
    public $employees;
    public $departments;
    public $branches;
    public $employee_id;
    public $name;
    public $middle_name;
    public $surname;
    public $gender;
    public $dob;
    public $address;
    public $post;
    public $department_id;
    public $branch_id;
    public $role_id;
    public $email;
    public $phonenumber;
    public $country;
    public $city;
    public $province;
    public $suburb;
    public $street_address;
    public $start_date;
    public $leave_days;

    public $title;
    public $file;

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
        $this->employees = Employee::all();
        $this->departments = Department::all();
        $this->branches = Branch::all();
      }
  
      private function resetInputFields(){
          $this->name = '';
          $this->middle_name = '';
          $this->surname = '';
          $this->dob = '';
          $this->gender = '';
          $this->phonenumber = '';
          $this->email = '';
          $this->idnumber = '';
          $this->post = '';
          $this->country = '';
          $this->city = '';
          $this->province = '';
          $this->suburb = '';
          $this->street_address = '';
          $this->zipcode = '';
          $this->start_date = '';
          $this->leave_days = '';
      }
      public function updated($value){
          $this->validateOnly($value);
      }
      protected $messages =[
        'title.0.required' => 'Title field is required',
        'file.0.required' => 'File field is required',
        'title.*.required' => 'Title field is required',
        'file.*.required' => 'File field is required',
        'department_id.*.required' => 'Select department',
        'branch_id.*.required' => 'Select branch',
    
    ];
      protected $rules = [
          'name' => 'required|alpha|min:2',
          'middle_name' => 'required|alpha|min:2',
          'surname' => 'required|alpha|min:2',
          'gender' => 'required',
          'dob' => 'required|date',
          'email' => 'required|email|unique:users,NULL,id,deleted_at,NULL',
          'phonenumber' => 'required|digits:10',
          'idnumber' => 'required|string|max:12',
          'country' => 'required|string',
          'city' => 'required',
          'province' => 'required',
          'suburb' => 'required',
          'street_address' => 'required',
          'zipcode' => 'required',
          'start_date' => 'required',
          'leave_days' => 'required',
          'post' => 'required',
          'branch_id' => 'required',
          'department_id' => 'required',
          'title.0' => 'nullable|string',
          'file.0' => 'nullable|file|mimes:docx,doc,pdf,xls,xlsx,pptx|max:10000',
          'title.*' => 'required',
          'file.*' => 'required',
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
       
        $initials = "TMC";
        $count = Count::latest()->first();
        if(!$count){
        Count::create([
            'count' => 1
        ]);
        $employee_number =  $initials . str_pad(1, 5, "0", STR_PAD_LEFT);
        }else{
        $count_number = $count->count + 1 ;
        $employee_number = $initials . str_pad($count_number, 5, "0", STR_PAD_LEFT);
        $count->count = $count_number;
        $count->update();
        } 
        return $employee_number;
    }

     public function create_form(){
        $this->dispatchBrowserEvent('show-employeeModal');
        }

      public function store(){
          $leave_type = new LeaveType;
          $leave_type->user_id = Auth::user()->id;
          $leave_type->name = $this->name;
          $leave_type->save();
          Session::flash('success','Leave Type saved successfully');
          $this->resetInputFields();
          $this->dispatchBrowserEvent('hide-leaveTypeModal',['message',"Leave Type successfully added"]);
          return redirect()->route('leave_types.index');
      }
    public function render()
    {
        return view('livewire.employees');
    }
}
