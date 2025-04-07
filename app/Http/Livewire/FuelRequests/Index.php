<?php

namespace App\Http\Livewire\FuelRequests;

use Livewire\Component;
use App\Models\Allocation;
use App\Models\FuelRequest;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class Index extends Component
{

    public $allocations;
    public $allocation_id;
    public $fuel_requests;
    public $fuel_request_id;
    public $request_type;
    public $request_number;
    public $status;
    public $employee_number;
    public $quantity;
    public $fuel_type;
    public $date;




    public $user_id;

    public function mount(){
        $this->allocations = Allocation::where('employee_id',Auth::user()->employee->id)->get();
        $this->fuel_requests = FuelRequest::where('employee_id', Auth::user()->employee->id)
        ->whereDate('created_at', \Carbon\Carbon::today())->get();
    }

    public function updated($value){
        $this->validateOnly($value);
    }
    protected $rules = [
      
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

        $request = FuelRequest::where('employee_id',Auth::user()->employee->id)->orderBy('id','desc')->first();

        if (!$request) {
            $request_number =  $initials .'FR'. str_pad(1, 5, "0", STR_PAD_LEFT);
        }else {
            $number = $request->id + 1;
            $request_number =  $initials .'FR'. str_pad($number, 5, "0", STR_PAD_LEFT);
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
            $fuel_request->request_type = $this->request_type;
            $fuel_request->fuel_type = $this->fuel_type;
            $fuel_request->quantity = $this->quantity;
            $fuel_request->date = $this->date;
            $fuel_request->status = "pending";
            $fuel_request->save();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Fuel Request Submitted Successfully!!"
            ]);

            $this->dispatchBrowserEvent('hide-modal');

            return redirect()->route('fuel_requests.index');
        }else {
            $this->dispatchBrowserEvent('alert',[
                'type'=>'erro',
                'message'=>"No active allocation assigned found"
            ]);
            return redirect()->route('fuel_requests.index');
        }


    }

    public function edit($id){
    $fuel_request = FuelRequest::find($id);
    $this->user_id = $fuel_request->user_id;
    $this->employee_id = $fuel_request->employee_id;
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
            $fuel_request = fuel_request::find($this->fuel_request_id);
            $fuel_request->update([
                'user_id' => Auth::user()->id,
                'employee_id' => Auth::user()->employee->id,
                'request_type' => $this->request_type,
                'fuel_type' => $this->fuel_type,
                'quantity' => $this->quantity,
                'date' => $this->date,
            ]);
            $this->updateMode = false;
            Session::flash('success','fuel_request updated successfully');

            $this->dispatchBrowserEvent('hide-fuel_requestEditModal',['message',"fuel_request successfully updated"]);
            return redirect()->route('fuel_requests.index');

        }
    }

    public function render()
    {
        $this->fuel_requests = FuelRequest::where('employee_id', Auth::user()->employee->id)
        ->whereDate('created_at', \Carbon\Carbon::today())->get();
        return view('livewire.fuel-requests.index',[
            'fuel_requests' => $this->fuel_requests
        ]);
    }
}
