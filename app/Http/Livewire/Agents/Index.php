<?php

namespace App\Http\Livewire\Agents;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Agent;
use Livewire\Component;
use App\Models\Document;
use Maatwebsite\Excel\Excel;
use App\Exports\AgentsExport;
use Livewire\WithFileUploads;
use App\Mail\AccountCreationMail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;

class Index extends Component
{
    use WithFileUploads;

    public $agents;
    public $name;
    public $surname;
    public $dob;
    public $gender;
    public $province;
    public $idnumber;
    public $phonenumber;
    public $email;
    public $country;
    public $city;
    public $suburb;
    public $street_address;

    public $agent_id;
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

    public function mount(){
        $this->agents = Agent::latest()->get();
    }

    
    public function exportAgentsCSV(Excel $excel){

        return $excel->download(new AgentsExport, 'agents.csv', Excel::CSV);
    }
    public function exportAgentsPDF(Excel $excel){

        return $excel->download(new AgentsExport, 'agents.pdf', Excel::DOMPDF);
    }
    public function exportAgentsExcel(Excel $excel){
        return $excel->download(new AgentsExport, 'agents.xlsx');
    }

    public function agentNumber(){
       
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

            $agent = Agent::orderBy('id', 'desc')->first();

        if (!$agent) {
            $agent_number =  $initials .'A'. str_pad(1, 5, "0", STR_PAD_LEFT);
        }else {
            $number = $agent->id + 1;
            $agent_number =  $initials .'A'. str_pad($number, 5, "0", STR_PAD_LEFT);
        }

        return  $agent_number;


    }

    private function resetInputFields(){
        $this->agents = "";
        $this->name = "";
        $this->surname = "";
        $this->dob = "";
        $this->phonenumber = "";
        $this->idnumber = "";
        $this->email = "";
        $this->country = "";
        $this->city = "";
        $this->suburb = "";
        $this->street_address = "";
        $this->title = "";
        $this->file = "";
        $this->expires_at = "";
    }
    public function updated($value){
        $this->validateOnly($value);
    }
    protected $rules = [
        'name' => 'required',
        'surname' => 'required',
        'dob' => 'required',
        'gender' => 'required',
        'idnumber' => 'required',
        'province' => 'required',
        'email' => 'required|email|unique:users,email,NULL,id,deleted_at,NULL',
        'phonenumber' => 'required',
        'country' => 'required|string|min:2',
        'city' => 'required|string|min:2',
        'suburb' => 'required|string|min:2',
        'street_address' => 'required|string|min:2',

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

    public function store(){
        try{

        $pin = $this->generatePIN();

        $user = new User;
        $user->name = $this->name;
        $user->category = 'agent';
        $user->email = $this->email;
        $user->password = Hash::make($pin);
        $user->save();

        if (isset(Auth::user()->company)) {
            $company = Auth::user()->company;
        }elseif (isset(Auth::user()->employee->company)) {
            $company = Auth::user()->employee->company;
        }

        Mail::to($this->email)->send(new AccountCreationMail($user, $company,$pin));

        $agent = new Agent;
        $agent->creator_id = Auth::user()->id;
        $agent->company_id = Auth::user()->employee->company->id;
        $agent->user_id = $user->id;
        $agent->agent_number = $this->agentNumber();
        $agent->name = $this->name;
        $agent->surname = $this->surname;
        $agent->dob = $this->dob;
        $agent->gender = $this->gender;
        $agent->idnumber = $this->idnumber;
        $agent->email = $this->email;
        $agent->pin = $pin;
        $agent->phonenumber = $this->phonenumber;
        $agent->country = $this->country;
        $agent->province = $this->province;
        $agent->city = $this->city;
        $agent->suburb = $this->suburb;
        $agent->street_address = $this->street_address;
        $agent->status = 1;
        $agent->save();

        if (isset($this->file) && isset($this->title) && $this->file != "") {
            // if ($this->file->count()>0) {
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
              $document->agent_id = $agent->id;
              $document->category = 'agent';
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
        //   }
        }
        $this->dispatchBrowserEvent('hide-agentModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Agent Created Successfully!!"
        ]);
        }
            catch(\Exception $e){
            // Set Flash Message
            $this->dispatchBrowserEvent('alert',[
                'type'=>'error',
                'message'=>"Something went wrong while creating agent!!"
            ]);
        }
    }

    public function edit($id){
    $agent = Agent::find($id);
    $this->user_id = $agent->user_id;
    $this->name = $agent->name;
    $this->surname = $agent->surname;
    $this->dob = $agent->dob;
    $this->email = $agent->email;
    $this->phonenumber = $agent->phonenumber;
    $this->idnumber = $agent->idnumber;
    $this->gender = $agent->gender;
    $this->country = $agent->country;
    $this->city = $agent->city;
    $this->suburb = $agent->suburb;
    $this->province = $agent->province;
    $this->street_address = $agent->street_address;
    $this->agent_id = $agent->id;
    $this->dispatchBrowserEvent('show-agentEditModal');

    }

    public function update()
    {
        if ($this->agent_id) {
            try{
            $agent = Agent::find($this->agent_id);
            $agent->creator_id = Auth::user()->id;
            $agent->user_id = $this->user_id;
            $agent->agent_number = $this->agentNumber();
            $agent->name = $this->name;
            $agent->surname = $this->surname;
            $agent->dob = $this->dob;
            $agent->gender = $this->gender;
            $agent->idnumber = $this->idnumber;
            $agent->email = $this->email;
            $agent->phonenumber = $this->phonenumber;
            $agent->country = $this->country;
            $agent->province = $this->province;
            $agent->city = $this->city;
            $agent->suburb = $this->suburb;
            $agent->street_address = $this->street_address;
            $agent->status = 1;
            $agent->update();
            $this->dispatchBrowserEvent('hide-agentEditModal');
            $this->resetInputFields();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Agent Updated Successfully!!"
            ]);
            }
                catch(\Exception $e){
                // Set Flash Message
                $this->dispatchBrowserEvent('alert',[
                    'type'=>'error',
                    'message'=>"Something went wrong while updating agent!!"
                ]);
            }

        }
    }
    public function render()
    {
        $this->agents = Agent::latest()->get();
        return view('livewire.agents.index',[
            'agents'=>$this->agents
        ]);
    }
}
