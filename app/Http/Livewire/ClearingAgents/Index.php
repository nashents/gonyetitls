<?php

namespace App\Http\Livewire\ClearingAgents;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Border;
use App\Models\Contact;
use Livewire\Component;
use App\Models\Document;
use Illuminate\Http\Request;
use App\Models\ClearingAgent;
use Livewire\WithFileUploads;
use App\Mail\AccountCreationMail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use Illuminate\Ixudra\Curl\Facades\Curl;

class Index extends Component
{
    use WithFileUploads;

    public $clearing_agents;
    public $contact_name;
    public $contact_surname;
    public $contact_email;
    public $contact_phonenumber;
    public $borders;
    public $border_id;
    public $department;
    public $name;
    public $phonenumber;
    public $worknumber;
    public $email;
    public $country;
    public $town;
    public $suburb;
    public $street_address;

    public $clearing_agent_id;
    public $user_id;


    public $title;
    public $file;
    public $expires_at;

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

    
    public $contacts_inputs = [];
    public $o = 1;
    public $m = 1;

    public function contactsAdd($o)
    {
        $o = $o + 1;
        $this->o = $o;
        array_push($this->contacts_inputs ,$o);
    }

    public function contactsRemove($o)
    {
        unset($this->contacts_inputs[$o]);
    }
    public $borders_inputs = [];
    public $z = 1;
    public $q = 1;

    public function bordersAdd($z)
    {
        $z = $z + 1;
        $this->z = $z;
        array_push($this->borders_inputs ,$z);
    }

    public function bordersRemove($z)
    {
        unset($this->borders_inputs[$z]);
    }


    public function mount(){
        $this->clearing_agents = ClearingAgent::latest()->get();
        $this->borders = Border::orderBy('name','asc')->get();
    }

    public function clearing_agentNumber(){
       
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

            $clearing_agent = ClearingAgent::orderBy('id', 'desc')->first();

        if (!$clearing_agent) {
            $clearing_agent_number =  $initials .'CA'. str_pad(1, 5, "0", STR_PAD_LEFT);
        }else {
            $number = $clearing_agent->id + 1;
            $clearing_agent_number =  $initials .'CA'. str_pad($number, 5, "0", STR_PAD_LEFT);
        }

        return  $clearing_agent_number;


    }

    private function resetInputFields(){
        $this->clearing_agents = "";
        $this->contact_name = "";
        $this->contact_surname = "";
        $this->contact_email = "";
        $this->contact_phonenumber = "";
        $this->department = "";
        $this->name = "";
        $this->border_id = "";
        $this->inputs = [];
        $this->contacts_inputs = [];
        $this->borders_inputs = [];
        $this->phonenumber = "";
        $this->worknumber = "";
        $this->email = "";
        $this->title = "";
        $this->file = "";
        $this->expires_at = "";
        $this->country = "";
        $this->town = "";
        $this->suburb = "";
        $this->street_address = "";
    }
    public function updated($value){
        $this->validateOnly($value);
    }
    protected $rules = [
        'name' => 'required|unique:clearing_agents,name,NULL,id,deleted_at,NULL|string|min:2',
        'border_id.0' => 'required',

    ];

   

    public function store(){
        try{
  
        $clearing_agent = new ClearingAgent;
        $clearing_agent->user_id = Auth::user()->id;
        $clearing_agent->name = $this->name;
        $clearing_agent->clearing_agent_number = $this->clearing_agentNumber();
        $clearing_agent->email = $this->email;
        $clearing_agent->phonenumber = $this->phonenumber;
        $clearing_agent->worknumber = $this->worknumber;
        $clearing_agent->country = $this->country;
        $clearing_agent->city = $this->town;
        $clearing_agent->suburb = $this->suburb;
        $clearing_agent->street_address = $this->street_address;
        $clearing_agent->status = 1;
        $clearing_agent->save();
        $clearing_agent->borders()->sync($this->border_id);
     

        if (isset($this->contact_name)) {
            foreach ($this->contact_name as $key => $value) {
               $contact = new Contact;
               $contact->clearing_agent_id = $clearing_agent->id;
               $contact->category = 'clearing_agent';
               if (isset($this->contact_name[$key])) {
                $contact->name = $this->contact_name[$key];
               }
               if (isset($this->contact_surname[$key])) {
                $contact->surname = $this->contact_surname[$key];
               }
                if (isset($this->contact_phonenumber[$key])) {
                    $contact->phonenumber = $this->contact_phonenumber[$key];
                }
                if (isset($this->contact_email[$key])) {
                    $contact->email = $this->contact_email[$key];
                }
                if (isset($this->department[$key])) {
                    $contact->department = $this->department[$key];
                }
              
               $contact->save();
            }
        }

        if (isset($this->file) && isset($this->title) && $this->file != "") {
            foreach ($this->file as $key => $value) {
              if(isset($this->file[$key])){
                  $file = $this->file[$key];
                  // get file with ext
                  $fileNameWithExt = $file->getClientOriginalName();
                  //get filename
                  $filename = pathinfo($fileNameWithExt, PATHINFO_FILENAME);
                  //get extention
                  $extention = $file->getClientOriginalExtension();
                  //file name to store
                  $fileNameToStore= $filename.'_'.time().'.'.$extention;
                  $file->storeAs('/documents', $fileNameToStore, 'my_files');

              }
              $document = new Document;
              $document->clearing_agent_id = $clearing_agent->id;
              $document->category = 'clearing_agent';
              if(isset($this->title[$key])){
              $document->title = $this->title[$key];
              }
              if (isset($fileNameToStore)) {
                  $document->filename = $fileNameToStore;
              }
              if(isset($this->expires_at[$key])){
                  $document->expires_at = Carbon::create($this->expires_at[$key])->toDateTimeString();
                  $today = now()->toDateTimeString();
                  $expire = Carbon::create($this->expires_at[$key])->toDateTimeString();
                  if ($today <=  $expire) {
                      $document->status = 1;
                  }else{
                      $document->status = 0;
                  }
              }else {
                $document->status = 1;
              }
              $document->save();

            }
                   # code...
        
        }

        $this->dispatchBrowserEvent('hide-clearing_agentModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Clearing Agent Created Successfully!!"
        ]);
        return redirect(request()->header('Referer'));
        }
            catch(\Exception $e){
            // Set Flash Message
            $this->dispatchBrowserEvent('alert',[
                'type'=>'error',
                'message'=>"Something went wrong while creating Clearing Agent!!"
            ]);
        }
    }

    public function edit($id){
    $clearing_agent = ClearingAgent::find($id);
    $this->user_id = $clearing_agent->user_id;
    $this->name = $clearing_agent->name;
    $this->border_id = $clearing_agent->border_id;
    $this->email = $clearing_agent->email;
    $this->phonenumber = $clearing_agent->phonenumber;
    $this->worknumber = $clearing_agent->worknumber;
    $this->country = $clearing_agent->country;
    $this->city = $clearing_agent->city;
    $this->suburb = $clearing_agent->suburb;
    $this->street_address = $clearing_agent->street_address;
    $this->clearing_agent_id = $clearing_agent->id;
    $this->dispatchBrowserEvent('show-clearing_agentEditModal');

    }

    public function update()
    {
        if ($this->clearing_agent_id) {
            try{
            $clearing_agent = ClearingAgent::find($this->clearing_agent_id);
            $clearing_agent->user_id = Auth::user()->id;
            $clearing_agent->name = $this->name;
            $clearing_agent->phonenumber = $this->phonenumber;
            $clearing_agent->worknumber = $this->worknumber;
            $clearing_agent->email = $this->email;
            $clearing_agent->country = $this->country;
            $clearing_agent->city = $this->town;
            $clearing_agent->suburb = $this->suburb;
            $clearing_agent->street_address = $this->street_address;
            $clearing_agent->update();

            $this->dispatchBrowserEvent('hide-clearing_agentEditModal');
            $this->resetInputFields();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Clearing Age Updated Successfully!!"
            ]);
            return redirect(request()->header('Referer'));
            }
                catch(\Exception $e){
                // Set Flash Message
                $this->dispatchBrowserEvent('alert',[
                    'type'=>'error',
                    'message'=>"Something went wrong while updating clearing agent!!"
                ]);
            }

        }
    }
    public function render()
    {
        $this->clearing_agents = ClearingAgent::latest()->get();
        return view('livewire.clearing-agents.index',[
            'clearing_agents'=>$this->clearing_agents
        ]);
    }
}
