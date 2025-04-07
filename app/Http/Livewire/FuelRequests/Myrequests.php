<?php

namespace App\Http\Livewire\FuelRequests;

use Livewire\Component;
use App\Models\Allocation;
use App\Models\FuelRequest;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class Myrequests extends Component
{

    public $allocations;
    public $selectedAllocation;
    public $fuel_requests;
    public $fuel_request_id;
    public $request_type;
    public $request_number;
    public $status;
    public $employee_number;
    public $employee_id;
    public $quantity;
    public $fuel_type;
    public $date;
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
        $this->fuel_balance = Auth::user()->employee->allocations->where('status',1)->sum('balance');
        $this->allocations = Allocation::where('employee_id',Auth::user()->employee->id)->get();
        $this->fuel_requests = FuelRequest::where('employee_id', $this->employee_id)->latest()->get();
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
        $allocation = Allocation::where('employee_id',Auth::user()->employee->id)
        ->where('status', 1)->latest()->first();
        if ($allocation) {
            $fuel_request = new FuelRequest;
            $fuel_request->user_id = Auth::user()->id;
            $fuel_request->employee_id = Auth::user()->employee->id;
            $fuel_request->request_number = $this->requestNumber();
            $fuel_request->allocation_id = $this->selectedAllocation;
            $fuel_request->request_type = $this->request_type;
            $fuel_request->fuel_type = $this->fuel_type;
            $fuel_request->quantity = $this->quantity;
            $fuel_request->date = $this->date;
            $fuel_request->status = "pending";
            $fuel_request->save();


            $this->dispatchBrowserEvent('hide-fuel_requestModal');
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Fuel Request Submitted Successfully!!"
            ]);
        }else {
            $this->dispatchBrowserEvent('hide-fuel_requestModal');
            $this->dispatchBrowserEvent('alert',[
                'type'=>'error',
                'message'=>"No active allocation assigned found!!"
            ]);

        }


    }

    public function edit($id){
        $fuel_request = FuelRequest::find($id);
        $this->user_id = $fuel_request->user_id;
        $this->employee_id = $fuel_request->employee_id;
        $this->selectedAllocation = $fuel_request->allocation_id;
        $this->request_type = $fuel_request->request_type;
        $this->fuel_type = $fuel_request->fuel_type;
        $this->quantity = $fuel_request->quantity;
        $this->date = $fuel_request->date;
        $this->fuel_request_id = $fuel_request->id;
        $this->dispatchBrowserEvent('show-fuel_requestEditModal');
        }


    public function update()
    {
        if ($this->fuel_request_id) {
            $fuel_request = FuelRequest::find($this->fuel_request_id);
            $fuel_request->user_id = Auth::user()->id;
            $fuel_request->employee_id = Auth::user()->employee->id;
            $fuel_request->allocation_id = $this->selectedAllocation;
            $fuel_request->request_type = $this->request_type;
            $fuel_request->fuel_type = $this->fuel_type;
            $fuel_request->quantity = $this->quantity;
            $fuel_request->date = $this->date;
            $fuel_request->update();

            $this->dispatchBrowserEvent('hide-fuel_requestEditModal');
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Fuel Request Updated Successfully!!"
            ]);

        }
    }

    public function render()
    {
        $this->fuel_requests = FuelRequest::where('employee_id', $this->employee_id)->latest()->get();
        $this->fuel_balance = Auth::user()->employee->allocations->where('status',1)->sum('balance');
        return view('livewire.fuel-requests.myrequests',[
            'fuel_requests' => $this->fuel_requests,
            'fuel_balance' => $this->fuel_balance,
        ]);
    }
}
