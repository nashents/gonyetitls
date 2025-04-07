<?php

namespace App\Http\Livewire\Compliances;

use App\Models\Route;
use App\Models\Driver;
use Livewire\Component;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\Compliance;
use Illuminate\Support\Facades\Auth;

class Index extends Component
{
    
    public $compliances;
    public $compliance;
    public $compliance_id;
    public $routes;
    public $route_id;
    public $customers;
    public $customer_id;
    public $drivers;
    public $driver_id;
    public $employees;
    public $employee_id;
    public $compliant;
    public $comments;
  

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
        $this->compliances = Compliance::orderBy('created_at','desc')->get();
        $this->routes = Route::orderBy('name','asc')->get();
        $this->customers = Customer::orderBy('name','asc')->get();
        $this->drivers = Driver::where('archive',0)->get();
        $this->employees = Employee::where('archive',0)->orderBy('name','asc')->get();
    }

    public function updated($value){
        $this->validateOnly($value);
    }
    protected $rules = [
        'compliant' => 'required',
        'route_id' => 'required',
        'customer_id' => 'required',
        'driver_id' => 'required',
        'employee_id' => 'required',
    ];

    private function resetInputFields(){
        $this->compliant = '';
        $this->comments = '';
        $this->route_id = '';
        $this->customer_id = '';
        $this->employee_id = '';
        $this->driver_id = '';
    }

  

    public function store(){

        try{

        $compliance = new Compliance;
        $compliance->user_id = Auth::user()->id;
        $compliance->employee_id = $this->employee_id;
        $compliance->driver_id = $this->driver_id;
        $compliance->route_id = $this->route_id;
        $compliance->customer_id = $this->customer_id;
        $compliance->compliant = $this->compliant;
        $compliance->comments = $this->comments;
        $compliance->save();

        $this->dispatchBrowserEvent('hide-complianceModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Compliance Created Successfully!!"
        ]);

      

        }
        catch(\Exception $e){
        // Set Flash Message
        $this->dispatchBrowserEvent('alert',[
            'type'=>'error',
            'message'=>"Something goes wrong while creating compliance!!"
        ]);
    }
    }

    public function edit($id){

    $compliance = Compliance::find($id);

    $this->comments = $compliance->comments;
    $this->compliant = $compliance->compliant;
    $this->employee_id = $compliance->employee_id;
    $this->driver_id = $compliance->driver_id;
    $this->route_id = $compliance->route_id;
    $this->customer_id = $compliance->customer_id;
    $this->compliance_id = $compliance->id;
    $this->dispatchBrowserEvent('show-complianceEditModal');

    }


    public function update()
    {
        if ($this->compliance_id) {
            try{
            $compliance = Compliance::find($this->compliance_id);
            $compliance->employee_id = $this->employee_id;
            $compliance->driver_id = $this->driver_id;
            $compliance->route_id = $this->route_id;
            $compliance->customer_id = $this->customer_id;
            $compliance->compliant = $this->compliant;
            $compliance->comments = $this->comments;
            $compliance->update();

            $this->dispatchBrowserEvent('hide-complianceEditModal');
            $this->resetInputFields();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Compliance Updated Successfully!!"
            ]);


            // return redirect()->route('compliances.index');
            }
            catch(\Exception $e){
            $this->dispatchBrowserEvent('hide-complianceEditModal');
            $this->dispatchBrowserEvent('alert',[
                'type'=>'error',
                'message'=>"Something goes wrong while creating compliance!!"
            ]);
          }
        }
    }


    public function render()
    {
        $this->compliances = Compliance::latest()->get();
        $this->routes = Route::orderBy('name','asc')->get();
        $this->customers = Customer::orderBy('name','asc')->get();
        $this->drivers = Driver::where('archive',0)->get();
        $this->employees = Employee::where('archive',0)->orderBy('name','asc')->get();
        return view('livewire.compliances.index',[
            'compliances'=>   $this->compliances,
            'routes'=>   $this->routes,
            'customers'=>   $this->customers,
            'drivers'=>   $this->drivers,
            'employees'=>   $this->employees,
        ]);
    }
}
