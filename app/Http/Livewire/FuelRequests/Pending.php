<?php

namespace App\Http\Livewire\FuelRequests;

use Livewire\Component;
use App\Models\FuelRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;

class Pending extends Component
{
    public $fuel_requests;
    public $authorize;
    public $comments;
    public $fuel_request_id;

    public $date;
    public $fullname;
    public $supplier;
    public $email;
    public $regnumber;
    public $authorized_by;
    public $fuel_type;
    public $quantity;
    public $driver;
    public $collection_point;
    public $delivery_point;

    public function mount(){
        $this->fuel_requests = FuelRequest::where('status', 'pending')->latest()->get();
    }

    public function decision($id){
        $fuel_request = FuelRequest::find($id);
        $this->fuel_request_id = $fuel_request->id;
        $this->dispatchBrowserEvent('show-fuelRequestAuthorizationModal');
    }

    public function update(){
        $fuel_request = FuelRequest::find($this->fuel_request_id);
        $fuel_request->status = $this->authorize;
        $fuel_request->comments = $this->comments;
        $fuel_request->update();

        $allocation = $fuel_request->employee->allocation;
        $allocation->balance  =   $allocation->balance - $fuel_request->quantity;
        $allocation->update();

        $this->email = $fuel_request->employee->allocation->container->vendor->email;
        $this->date = $fuel_request->date;
        $this->supplier = $fuel_request->employee->allocation->container->vendor->name;
        $this->driver = $fuel_request->employee->name .' '. $fuel_request->employee->surname;
        $this->fuel_type = $fuel_request->fuel_type;
        $this->quantity = $fuel_request->quantity;
        $this->authorized_by = Auth::user()->employee->name . ' ' . Auth::user()->employee->surname;
        $this->regnumber = $fuel_request->employee->allocation->vehicle->registration_number;
        if ($fuel_request->status =! "approved") {

        if ($this->authorize == "approved") {
            $data = array(
                'email'=> $this->email,
                'employee_email'=> $fuel_request->employee->email,
                'date'=> $this->date,
                'supplier'=> $this->supplier,
                'driver'=> $this->driver,
                'regnumber'=> $this->regnumber,
                'authorized_by'=> $this->authorized_by,
                'fuel_type'=> $this->fuel_type,
                'quantity'=> $this->quantity,
                'from'=> 'no-reply@tinmac.com',
                'subject'=> 'Auto generated fuel request confirmation'

               );
             Mail::send('emails.fuel_requests',$data, function($message) use($data){
                 $message->to($data['email']);
                 $message->cc($data['employee_email']);
                 $message->from($data['from']);
                 $message->subject($data['subject']);
             });

            $this->dispatchBrowserEvent('hide-fuelRequestAuthorizationModal');
            Session::flash('success','Fuel Request approved successfully');
            return redirect()->route('fuel_requests.approved');
            
        }else {
            $this->dispatchBrowserEvent('hide-fuelRequestAuthorizationModal');
            Session::flash('success','Fuel Request rejected successfully');
            return redirect()->route('fuel_requests.rejected');
        }
    }else {
        Session::flash('error','Authorization already approved');
        if ($this->authorize == 'approved') {
            return redirect()->route('fuel_requests.approved');
        }else {
            return redirect()->route('fuel_requests.rejected');
        }
    }

    }

    public function render()
    {
        return view('livewire.fuel-requests.pending');
    }
}
