<?php

namespace App\Http\Livewire\Compliances;

use App\Models\Compliance;
use App\Models\Customer;
use App\Models\Driver;
use App\Models\Employee;
use App\Models\Route;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    
    use WithPagination;
    protected $paginationTheme = 'bootstrap';
    public $search;
    protected $queryString = ['search'];
    protected $compliances;
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


      public function refresh($category){

        if($category == "routes"){
            $this->routes = Route::orderBy('name','asc')->get();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Routes Refreshed Successfully!!."
            ]);
        }
       
        elseif($category == "employees"){
            $this->employees = Employee::orderBy('name','asc')->orderBy('surname','asc')->get();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Employees Refreshed Successfully!!."
            ]);
        }
       
        elseif($category == "customers"){
            $this->customers = Customer::orderBy('name','asc')->get();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Customers Refreshed Successfully!!."
            ]);
        }
       
       elseif ($category == "drivers") {
            $this->drivers = Driver::query()
                ->where('archive', 0)
                ->whereHas('employee') // optional safety: only drivers that actually have an employee
                ->with('employee:id,name,surname') // eager load to avoid N+1
                ->join('employees', 'employees.id', '=', 'drivers.employee_id') // adjust FK if yours differs
                ->orderBy('employees.name')
                ->orderBy('employees.surname')
                ->select('drivers.*') // avoid column collisions
                ->get();

            $this->dispatchBrowserEvent('alert', [
                'type'    => 'success',
                'message' => "Drivers Refreshed Successfully!!."
            ]);
        }
       
       
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
        
        $base = Compliance::query()
        ->with([
            'user:id,name,surname',
            'employee:id,name,surname',
            'driver:id,employee_id', // driver itself has no name
            'route:id,name',
            'customer:id,name,surname',
        ]);

    $search = trim($this->search);

    $compliances = $base
        ->when($search !== '', function ($q) use ($search) {
            $q->where(function ($q) use ($search) {

                // ✅ direct columns on compliances table
                $q->where('comments', 'like', "%{$search}%")
                ->orWhere('compliant', 'like', "%{$search}%");

                // ✅ relations
                $q->orWhereHas('user', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                    ->orWhere('surname', 'like', "%{$search}%");
                });

                $q->orWhereHas('employee', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                    ->orWhere('surname', 'like', "%{$search}%");
                });
                $q->orWhereHas('driver.employee', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                    ->orWhere('surname', 'like', "%{$search}%");
                });

                $q->orWhereHas('route', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                });

                $q->orWhereHas('customer', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                    ->orWhere('surname', 'like', "%{$search}%");
                });
            });
        })
        ->latest()
        ->paginate(10); // ✅ keeps search in pagination links

    return view('livewire.compliances.index', [
        'compliances' => $compliances,
    ]);
    }
}
