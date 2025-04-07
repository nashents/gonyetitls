<?php

namespace App\Http\Livewire\Contacts;

use Carbon\Carbon;
use App\Models\Agent;
use App\Models\Broker;
use App\Models\Vendor;
use App\Models\Contact;
use App\Models\Vehicle;
use Livewire\Component;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\Incident;
use App\Models\Consignee;
use App\Models\TruckStop;
use App\Models\Transporter;
use App\Models\ClearingAgent;
use Livewire\WithFileUploads;

class Index extends Component
{
    use WithFileUploads;

    public $category;
    public $consignee;
    public $consignee_id;
    public $vendor;
    public $vendor_id;
    public $incident;
    public $incident_id;
    public $truck_stop;
    public $truck_stop_id;
    public $broker;
    public $broker_id;
    public $clearing_agent;
    public $clearing_agent_id;
    public $transporter;
    public $transporter_id;
    public $customer;
    public $customer_id;
    public $user_id;
    public $contacts;
    public $contact_id;

    public $name;
    public $surname;
    public $email;
    public $phonenumber;
    public $department;

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

    private function resetInputFields(){
        $this->title = "" ;
        $this->file = "";
    }


    public function mount($id,$category){
        $this->category = $category;
    if ($this->category == "customer") {
        $this->customer = Customer::find($id);
        $this->contacts = Contact::where('category', $this->category)
        ->where('customer_id', $this->customer->id)->latest()->get();
    }
   
    elseif ($this->category == "vendor") {
        $this->vendor = Vendor::find($id);
        $this->contacts = Contact::where('category', $this->category)
        ->where('vendor_id', $this->vendor->id)->latest()->get();
    }
    elseif ($this->category == "consignee") {
        $this->consignee = Consignee::find($id);
        $this->contacts = Contact::where('category', $this->category)
        ->where('consignee_id', $this->consignee->id)->latest()->get();
    }
    elseif ($this->category == "incident") {
        $this->incident = Incident::find($id);
        $this->contacts = Contact::where('category', $this->category)
        ->where('incident_id', $this->incident->id)->latest()->get();
    }
    elseif ($this->category == "truck_stop") {
        $this->truck_stop = TruckStop::find($id);
        $this->contacts = Contact::where('category', $this->category)
        ->where('truck_stop_id', $this->truck_stop->id)->latest()->get();
    }
    elseif ($this->category == "broker") {
        $this->broker = Broker::find($id);
        $this->contacts = Contact::where('category', $this->category)
        ->where('broker_id', $this->broker->id)->latest()->get();
    }
    elseif ($this->category == "transporter") {
        $this->transporter = Transporter::find($id);
        $this->contacts = Contact::where('category', $this->category)
        ->where('transporter_id', $this->transporter->id)->latest()->get();
    }
    elseif ($this->category == "clearing_agent") {
        $this->clearing_agent = ClearingAgent::find($id);
        $this->contacts = Contact::where('category', $this->category)
        ->where('clearing_agent_id', $this->clearing_agent->id)->latest()->get();
    }
   
    

    }

    public function store(){
        try{

            if (isset($this->name)) {
                foreach ($this->name as $key => $value) {

            $contact = new Contact;
            $contact->category = $this->category;
            if (isset($this->customer)) {
                $contact->customer_id = $this->customer->id;
            }
            elseif (isset($this->transporter)) {
                $contact->transporter_id = $this->transporter->id;
            }
            elseif (isset($this->truck_stop)) {
                $contact->truck_stop_id = $this->truck_stop->id;
            }
            elseif (isset($this->incident)) {
                $contact->incident_id = $this->incident->id;
            }
            elseif (isset($this->consignee)) {
                $contact->consignee_id = $this->consignee->id;
            }
            elseif (isset($this->broker)) {
                $contact->broker_id = $this->broker->id;
            }
            elseif (isset($this->vendor)) {
                $contact->vendor_id = $this->vendor->id;
            }
            elseif (isset($this->clearing_agent)) {
                $contact->clearing_agent_id = $this->clearing_agent->id;
            }
            if(isset($this->name[$key])){
                $contact->name = $this->name[$key];
            }
            if(isset($this->surname[$key])){
                $contact->surname = $this->surname[$key];
            }
            if(isset($this->email[$key])){
                $contact->email = $this->email[$key];
            }
            if(isset($this->phonenumber[$key])){
                $contact->phonenumber = $this->phonenumber[$key];
            }
            if(isset($this->department[$key])){
                $contact->department = $this->department[$key];
            }


            $contact->save();

                }
            }

            $this->dispatchBrowserEvent('hide-contactModal');
            $this->resetInputFields();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Contact(s) Uploaded Successfully!!"
            ]);

        }catch(\Exception $e){
            // Set Flash Message
            $this->dispatchBrowserEvent('alert',[
                'type'=>'error',
                'message'=>"Something went wrong while uploading contact(s)!!"
            ]);
        }
    }

    public function edit($id){

        $contact = Contact::find($id);
        $this->user_id = $contact->user_id;
        $this->customer_id = $contact->customer_id;
        $this->vendor_id = $contact->vendor_id;
        $this->broker_id = $contact->broker_id;
        $this->transporter_id = $contact->transporter_id;
        $this->incident_id = $contact->incident_id;
        $this->consignee_id = $contact->consignee_id;
        $this->truck_stop_id = $contact->truck_stop_id;
        $this->clearing_agent_id = $contact->clearing_agent_id;
        $this->name = $contact->name;
        $this->surname = $contact->surname;
        $this->email = $contact->email;
        $this->phonenumber = $contact->phonenumber;
        $this->department = $contact->department;
        $this->contact_id = $contact->id;

        $this->dispatchBrowserEvent('show-contactEditModal');

        }

        public function update()
        {
            if ($this->contact_id) {
                try{
                  
                $contact = contact::find($this->contact_id);    
                if (isset($this->customer_id)) {
                    $contact->customer_id = $this->customer_id;
                }
                elseif (isset($this->transporter_id)) {
                    $contact->transporter_id = $this->transporter_id;
                }
                elseif (isset($this->broker_id)) {
                    $contact->broker_id = $this->broker_id;
                }
                elseif (isset($this->vendor_id)) {
                    $contact->vendor_id = $this->vendor_id;
                }
                elseif (isset($this->consignee_id)) {
                    $contact->consignee_id = $this->consignee_id;
                }
                elseif (isset($this->truck_stop_id)) {
                    $contact->truck_stop_id = $this->truck_stop_id;
                }
                elseif (isset($this->incident_id)) {
                    $contact->incident_id = $this->incident_id;
                }
                elseif (isset($this->clearing_agent_id)) {
                    $contact->clearing_agent_id = $this->clearing_agent_id;
                }
                $contact->name = $this->name;
                $contact->surname = $this->surname;
                $contact->email = $this->email;
                $contact->phonenumber = $this->phonenumber;
                $contact->department = $this->department;
                $contact->update();

                $this->dispatchBrowserEvent('hide-contactEditModal');
                $this->resetInputFields();
                $this->dispatchBrowserEvent('alert',[
                    'type'=>'success',
                    'message'=>"Contact Updated Successfully!!"
                ]);
            }catch(\Exception $e){
                // Set Flash Message
                $this->dispatchBrowserEvent('alert',[
                    'type'=>'error',
                    'message'=>"Something went wrong while updating contact(s)!!"
                ]);
            }

            }
        }


    public function render()
    {
        if ($this->category == "customer") {
            $this->contacts = Contact::where('category', $this->category)
            ->where('customer_id', $this->customer->id)->latest()->get();
        }elseif ($this->category == "transporter") {
            $this->contacts = Contact::where('category', $this->category)
            ->where('transporter_id', $this->transporter->id)->latest()->get();
        }  
        elseif ($this->category == "vendor") {
            $this->contacts = Contact::where('category', $this->category)
            ->where('vendor_id', $this->vendor->id)->latest()->get();
        }
        elseif ($this->category == "broker") {
            $this->contacts = Contact::where('category', $this->category)
            ->where('broker_id', $this->broker->id)->latest()->get();
        }
        elseif ($this->category == "consignee") {
            $this->contacts = Contact::where('category', $this->category)
            ->where('consignee_id', $this->consignee->id)->latest()->get();
        }
        elseif ($this->category == "truck_stop") {
            $this->contacts = Contact::where('category', $this->category)
            ->where('truck_stop_id', $this->truck_stop->id)->latest()->get();
        }
        elseif ($this->category == "clearing_agent") {
            $this->contacts = Contact::where('category', $this->category)
            ->where('clearing_agent_id', $this->clearing_agent->id)->latest()->get();
        }
        elseif ($this->category == "incident") {
            $this->contacts = Contact::where('category', $this->category)
            ->where('incident_id', $this->incident->id)->latest()->get();
        }
     
        return view('livewire.contacts.index',[
            'contacts'=> $this->contacts
        ]);
    }
}
