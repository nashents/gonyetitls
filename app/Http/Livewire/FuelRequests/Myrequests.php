<?php

namespace App\Http\Livewire\FuelRequests;

use App\Models\Allocation;
use App\Models\Asset;
use App\Models\Employee;
use App\Models\FuelRequest;
use App\Models\Horse;
use App\Models\Vehicle;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class Myrequests extends Component
{

    use WithFileUploads;

    use WithPagination;

    protected $paginationTheme = 'bootstrap';
    public $search;
    protected $queryString = ['search'];
    public $allocations;
    public $selectedAllocation;
    protected $fuel_requests;
    public $fuel_request_id;
    public $request_type;
    public $request_number;
    public $status;
    public $employee_number;
    public $employee_id;
    public $employees;
    public $selectedEmployee;
    public $horses;
    public $selectedHorse;
    public $vehicles;
    public $selectedVehicle;
    public $assets;
    public $selectedAsset;
    public $quantity;
    public $fuel_type;
    public $category = "Horse";
    public $from_allocation = False;
    public $date;
    public $reason;
    public $fuel_balance = 0;


    public function updatedSelectedAllocation($id){
        if (!is_null($id)) {
            $allocation = Allocation::find($id);
            if (isset($allocation)) {
                $this->fuel_type = $allocation->fuel_type;
            }
            
        }
    }

    public $user_id;

    public function mount($id){
        $this->employee_id = $id;
        $this->selectedEmployee = $id;
        $this->fuel_balance = Auth::user()->employee->allocations->where('status',1)->sum('balance');
        $this->allocations = Allocation::where('employee_id',Auth::user()->employee->id)->get();
       
        $this->horses = Horse::where('archive',0)->orderBy('registration_number','asc')->get();
        $this->vehicles = Vehicle::where('archive',0)->orderBy('registration_number','asc')->get();
        $this->employees = Employee::where('archive',0)->orderBy('name','asc')->orderBy('surname','asc')->get();
        $this->assets = Asset::where('disposed',0)->latest()->get();
    }

    public function updated($value){
        $this->validateOnly($value);
    }
    protected $rules = [
        'selectedAllocation' => 'required',
        'fuel_type' => 'required',
        'request_type' => 'required',
        'date' => 'required',
        'quantity' => 'required',
    ];
    public function requestNumber(){

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

        $last_request_id = FuelRequest::where('employee_id',Auth::user()->employee->id)->latest()->pluck('id')->first();

        if (!$last_request_id) {
            $request_number =  $initials .'FR'. str_pad(1, 5, "0", STR_PAD_LEFT);
        }else {
            $request_number = $last_request_id + 1;
            $request_number =  $initials .'FR'. str_pad($request_number, 5, "0", STR_PAD_LEFT);
        }

        return  $request_number;


    }
    public function store(){
      
            $fuel_request = new FuelRequest;
            $fuel_request->user_id = Auth::user()->id;
            $fuel_request->request_number = $this->requestNumber();
            $fuel_request->employee_id = $this->selectedEmployee ?: Null;

            $fuel_request->horse_id = $this->selectedHorse ?: Null;
            $fuel_request->vehicle_id = $this->selectedVehicle ?: Null;
            $fuel_request->asset_id = $this->selectedAsset ?: Null;
            $fuel_request->allocation_id = $this->selectedAllocation;
            $fuel_request->request_type = $this->request_type;
            $fuel_request->fuel_type = $this->fuel_type;
            $fuel_request->quantity = $this->quantity;
            $fuel_request->reason = $this->reason;
            $fuel_request->date = $this->date;
            $fuel_request->status = "pending";
            $fuel_request->save();


            $this->dispatchBrowserEvent('hide-fuel_requestModal');
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Fuel Request Submitted Successfully!!"
            ]);
       


    }

    public function edit($id){

        $fuel_request = FuelRequest::find($id);
        $this->user_id = $fuel_request->user_id;
        $this->employee_id = $fuel_request->employee_id;
        $this->selectedAllocation = $fuel_request->allocation_id;
        $this->request_type = $fuel_request->request_type;
        $this->fuel_type = $fuel_request->fuel_type;
        $this->reason = $fuel_request->reason;
        $this->selectedEmployee = $fuel_request->employee_id;
        $this->selectedHorse = $fuel_request->horse_id;
        $this->selectedVehicle = $fuel_request->vehicle_id;
        $this->selectedAsset = $fuel_request->asset_id;
        $this->quantity = $fuel_request->quantity;
        $this->date = $fuel_request->date;
        $this->fuel_request_id = $fuel_request->id;
        $this->dispatchBrowserEvent('show-fuel_requestEditModal');

    }


    public function update()
    {
        if ($this->fuel_request_id) {
            $fuel_request = FuelRequest::find($this->fuel_request_id);
            $fuel_request->horse_id = $this->selectedHorse ?: Null;
            $fuel_request->vehicle_id = $this->selectedVehicle ?: Null;
            $fuel_request->asset_id = $this->selectedAsset ?: Null;
            $fuel_request->allocation_id = $this->selectedAllocation;
            $fuel_request->request_type = $this->request_type;
            $fuel_request->fuel_type = $this->fuel_type;
            $fuel_request->quantity = $this->quantity;
            $fuel_request->reason = $this->reason;
            $fuel_request->date = $this->date;
            $fuel_request->status = "pending";
            $fuel_request->update();

            $this->dispatchBrowserEvent('hide-fuel_requestEditModal');
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Fuel Request Updated Successfully!!"
            ]);

        }
    }

    public function delete($id){
        $this->fuel_request_id = $id;
        $this->dispatchBrowserEvent('show-deleteModal');
    }
    
    public function destroy(){
        $request = FuelRequest::find($this->fuel_request_id);
        $request->delete();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Fuel Request Deleted Successfully!!"
        ]);

        $this->dispatchBrowserEvent('show-deleteModal');
    }

    public function render()
    {
       
        $query = FuelRequest::query()
            ->with([
                'employee',
                'horse',
                'vehicle',
                'asset',
            ])
            ->where('employee_id', $this->selectedEmployee)

            // Date Filtering
            ->when(
                isset($this->from_date) && isset($this->to_date)
                    && $this->from_date && $this->to_date,

                function ($q) {

                    $q->whereBetween('created_at', [
                        Carbon::parse($this->from_date)->startOfDay(),
                        Carbon::parse($this->to_date)->endOfDay(),
                    ]);
                },

                function ($q) {

                    // Default: Current Month
                    $q->whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year);
                }
            )

            // Search
            ->when($this->search, function ($q) {

                $search = '%' . $this->search . '%';

                $q->where(function ($subQuery) use ($search) {

                    $subQuery->where('reason', 'like', $search)
                        ->orWhere('fuel_type', 'like', $search)

                        // Quantity numeric-safe
                        ->orWhereRaw('CAST(quantity AS CHAR) LIKE ?', [$search])

                        // Employee
                        ->orWhereHas('employee', function ($employeeQuery) use ($search) {
                            $employeeQuery->where('name', 'like', $search)
                                ->orWhere('employee_number', 'like', $search);
                        })

                        // Horse
                        ->orWhereHas('horse', function ($horseQuery) use ($search) {
                            $horseQuery->where('registration_number', 'like', $search)
                                ->orWhere('fleet_number', 'like', $search)
                                ->orWhere('make', 'like', $search)
                                ->orWhere('model', 'like', $search);
                        })

                        // Vehicle
                        ->orWhereHas('vehicle', function ($vehicleQuery) use ($search) {
                            $vehicleQuery->where('registration_number', 'like', $search)
                                ->orWhere('fleet_number', 'like', $search)
                                ->orWhere('make', 'like', $search)
                                ->orWhere('model', 'like', $search);
                        })

                        // Asset
                        ->orWhereHas('asset', function ($assetQuery) use ($search) {
                            $assetQuery->where('name', 'like', $search)
                                ->orWhere('serial_number', 'like', $search)
                                ->orWhere('asset_number', 'like', $search);
                        });
                });
            })

            ->latest()
            ->paginate(10);
   
        $this->fuel_balance = Auth::user()->employee->allocations->where('status',1)->sum('balance');
        return view('livewire.fuel-requests.myrequests',[
            'fuel_requests' => $query,
            'fuel_balance' => $this->fuel_balance,
        ]);
    }
}
