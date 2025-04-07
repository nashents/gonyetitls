<?php

namespace App\Http\Livewire\FuelRequests;

use Livewire\Component;
use App\Models\FuelRequest;
use Illuminate\Support\Facades\Session;

class Manage extends Component
{
    public $fuel_requests;
    public $fuel_request_id;
    public $status;
    public $decision;
    public $comments;
    public $user_id;

    public function mount(){
        $this->fuel_requests = FuelRequest::latest()->get();
    }
    public function decision($id){
        $fuel_request = FuelRequest::find($id);
        $this->user_id = $fuel_request->user_id;
        $this->employee_id = $fuel_request->employee_id;
        $this->fuel_request_id = $fuel_request->id;
        $this->dispatchBrowserEvent('show-fuel_requestDecisionModal');
        }

        public function update(){
            $fuel_request = FuelRequest::find($this->fuel_request_id);
            $fuel_request->comments = $this->comments;
            $fuel_request->status = $this->decision;
            $fuel_request->update();
            if ($this->decision == "approved") {
                $employee = $fuel_request->employee;
                $allocation = $employee->allocation->latest()->first();
                $allocation->balance = $allocation->balance - $fuel_request->quantity;
                $allocation->update();
            }
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Decision Effected Successfully!!"
            ]);
            $this->dispatchBrowserEvent('hide-fuel_requestDecisionModal');
            return redirect()->route('fuel_requests.manage');
        }

    public function render()
    {
        return view('livewire.fuel-requests.manage');
    }
}
