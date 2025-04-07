<?php

namespace App\Http\Livewire\Brokers;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Broker;
use App\Models\Contact;
use Livewire\Component;
use App\Models\Document;
use Maatwebsite\Excel\Excel;
use Livewire\WithFileUploads;
use App\Exports\BrokersExport;
use App\Mail\AccountCreationMail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;

class Index extends Component
{

    use WithFileUploads;

    public $brokers;
    public $contact_name;
    public $contact_surname;
    public $contact_email;
    public $contact_phonenumber;
    public $department;
    public $name;
    public $phonenumber;
    public $worknumber;
    public $email;
    public $country;
    public $city;
    public $suburb;
    public $street_address;

    public $broker_id;
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

    public function exportBrokersCSV(Excel $excel){

        return $excel->download(new BrokersExport, 'brokers.csv', Excel::CSV);
    }
    public function exportBrokersPDF(Excel $excel){

        return $excel->download(new BrokersExport, 'brokers.pdf', Excel::DOMPDF);
    }
    public function exportBrokersExcel(Excel $excel){
        return $excel->download(new BrokersExport, 'brokers.xlsx');
    }

    public function brokerNumber(){

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

            $broker = Broker::orderBy('id','desc')->first();

        if (!$broker) {
            $broker_number =  $initials .'B'. str_pad(1, 5, "0", STR_PAD_LEFT);
        }else {
            $broker_number = $broker->id + 1;
            $broker_number =  $initials .'B'. str_pad($broker_number, 5, "0", STR_PAD_LEFT);
        }

        return  $broker_number;


    }

    public function mount(){
        $this->brokers = Broker::latest()->get();
    }

    public function updated($value){
        $this->validateOnly($value);
    }
    private function resetInputFields(){

        $this->contact_name = "";
        $this->contact_surname = "";
        $this->contact_email = "";
        $this->contact_phonenumber = "";
        $this->department = "";
        $this->name = "";
        $this->phonenumber = "";
        $this->worknumber = "";
        $this->email = "";
        $this->country = "";
        $this->city = "";
        $this->suburb = "";
        $this->street_address = "";
        $this->title = "";
        $this->file = "";
        $this->expires_at = "";
    }
    protected $rules = [
        'name' => 'required|unique:brokers,name,NULL,id,deleted_at,NULL|string|min:2',
        'email' => 'required|email|unique:brokers,name,NULL,id,deleted_at,NULL|string|min:2',
        'phonenumber' => 'required',
        'worknumber' => 'required',
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
        // try {
            $pin = $this->generatePIN();

            $user = new User;
            $user->name = $this->name;
            $user->category = 'broker';
            $user->email = $this->email;
            $user->password = Hash::make($pin);
            $user->save();
    
            if (isset(Auth::user()->company)) {
                $company = Auth::user()->company;
            }elseif (isset(Auth::user()->employee->company)) {
                $company = Auth::user()->employee->company;
            }
    
        Mail::to($this->email)->send(new AccountCreationMail($user, $company,$pin));

        $broker = new Broker;
        $broker->user_id = Auth::user()->id;
        $broker->broker_number = $this->brokerNumber();
        $broker->name = $this->name;
        $broker->email = $this->email;
        $broker->pin = $pin;
        $broker->phonenumber = $this->phonenumber;
        $broker->worknumber = $this->worknumber;
        $broker->country = $this->country;
        $broker->city = $this->city;
        $broker->suburb = $this->suburb;
        $broker->street_address = $this->street_address;
        $broker->status = 1;
        $broker->save();
        
        if (isset($this->contact_name)) {
            foreach ($this->contact_name as $key => $value) {
               $contact = new Contact;
               $contact->broker_id = $broker->id;
               $contact->category = 'broker';
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
              $document->broker_id = $broker->id;
              $document->category = 'broker';
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

            // }
                   # code...
          }
        }

        $this->dispatchBrowserEvent('hide-brokerModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Broker Created Successfully!!"
        ]);
    //     }
    //     catch(\Exception $e){
    //     // Set Flash Message
    //     $this->dispatchBrowserEvent('hide-brokerModal');
    //     $this->dispatchBrowserEvent('alert',[

    //         'type'=>'error',
    //         'message'=>"Something went wrong while creating broker!!"
    //     ]);
    // }
    }

    public function edit($id){
    $broker = Broker::find($id);
    $this->user_id = $broker->user_id;
    $this->name = $broker->name;
    $this->email = $broker->email;
    $this->phonenumber = $broker->phonenumber;
    $this->worknumber = $broker->worknumber;
    $this->country = $broker->country;
    $this->city = $broker->city;
    $this->suburb = $broker->suburb;
    $this->street_address = $broker->street_address;
    $this->broker_id = $broker->id;
    $this->dispatchBrowserEvent('show-brokerEditModal');

    }

    public function update()
    {
        if ($this->broker_id) {
            try {
            $broker = Broker::find($this->broker_id);
            $broker->update([
                'user_id' => Auth::user()->id,
                'name' => $this->name,
                'phonenumber' => $this->phonenumber,
                'worknumber' => $this->worknumber,
                'email' => $this->email,
                'country' => $this->country,
                'city' => $this->city,
                'suburb' => $this->suburb,
                'street_address' => $this->street_address,
              
            ]);
            $this->dispatchBrowserEvent('hide-brokerEditModal');
            $this->resetInputFields();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Broker Updated Successfully!!"
            ]);
              }catch(\Exception $e){
                // Set Flash Message
                $this->dispatchBrowserEvent('hide-brokerEditModal');
                $this->dispatchBrowserEvent('alert',[

                    'type'=>'error',
                    'message'=>"Something went wrong while updating broker!!"
                ]);
            }
        }
    }
    public function render()
    {
        $this->brokers = Broker::latest()->get();
        return view('livewire.brokers.index',[
            'brokers' => $this->brokers
        ]);
    }
}
