<?php

namespace App\Http\Livewire\Transporters;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Cargo;
use App\Models\Contact;
use Livewire\Component;
use App\Models\Corridor;
use App\Models\Document;
use App\Models\Transporter;
use Livewire\WithFileUploads;
use App\Mail\AccountCreationMail;
use App\Models\TransporterContact;
use App\Exports\TransportersExport;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Excel;
use Illuminate\Support\Facades\Session;

class Index extends Component
{
    use WithFileUploads;



    public $cargos;
    public $cargo_id;
    public $corridors;
    public $corridor_id;
    public $transporters;
    public $contact_name;
    public $contact_surname;
    public $contact_email;
    public $contact_phonenumber;
    public $department;
    public $name;
    public $phonenumber;
    public $email;
    public $country;
    public $city;
    public $suburb;
    public $street_address;
    public $worknumber;

    public $transporter_id;
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

    public $corridors_inputs = [];
    public $l = 1;
    public $j = 1;

    public function corridorsAdd($l)
    {
        $l = $l + 1;
        $this->l = $l;
        array_push($this->corridors_inputs ,$l);
    }

    public function corridorsRemove($l)
    {
        unset($this->corridors_inputs[$l]);
    }

    public $cargos_inputs = [];
    public $s = 1;
    public $r = 1;

    public function cargosAdd($s)
    {
        $s = $s + 1;
        $this->s = $s;
        array_push($this->cargos_inputs ,$s);
    }

    public function cargosRemove($s)
    {
        unset($this->cargos_inputs[$s]);
    }


    public function exportTransportersCSV(Excel $excel){

        return $excel->download(new TransportersExport, 'transporters.csv', Excel::CSV);
    }
    public function exportTransportersPDF(Excel $excel){

        return $excel->download(new TransportersExport, 'transporters.pdf', Excel::DOMPDF);
    }
    public function exportTransportersExcel(Excel $excel){
        return $excel->download(new TransportersExport, 'transporters.xlsx');
    }

    public function mount(){
        $this->transporters = Transporter::latest()->get();
        $this->corridors = Corridor::latest()->get();
        $this->cargos = Cargo::latest()->get();
    }

    public function transporterNumber(){

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

            $transporter = Transporter::orderBy('id','desc')->first();

        if (!$transporter) {
            $transporter_number =  $initials .'T'. str_pad(1, 5, "0", STR_PAD_LEFT);
        }else {
            $number = $transporter->id + 1;
            $transporter_number =  $initials .'T'. str_pad($number, 5, "0", STR_PAD_LEFT);
        }

        return  $transporter_number;


    }

    private function resetInputFields(){
        $this->transporters = "";
        $this->contact_name = [];
        $this->contact_surname = [];
        $this->contact_email = [];
        $this->contact_phonenumber = [];
        $this->department = [];
        $this->name = "";
        $this->phonenumber = "";
        $this->worknumber = "";
        $this->email = "";
        $this->country = "";
        $this->city = "";
        $this->suburb = "";
        $this->street_address = "";
        $this->cargo_id = [];
        $this->corridor_id = [];
        $this->title = [];
        $this->file = [];
        $this->expires_at = [];
    }
    public function updated($value){
        $this->validateOnly($value);
    }
    protected $rules = [
        'name' => 'required|unique:transporters,name,NULL,id,deleted_at,NULL|string|min:2',
        'email' => 'required|email|unique:users,email,NULL,id,deleted_at,NULL',
        'phonenumber' => 'required',
        'worknumber' => 'required',
        'contact_name.0' => 'required|string|min:2',
        'contact_surname.0' => 'required|string|min:2',
        'contact_email.0' => 'required|email',
        'contact_phonenumber.0' => 'required|string|min:2',
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
        // try{

        $pin = $this->generatePIN();

        $user = new User;
        $user->name = $this->name;
        $user->category = 'transporter';
        $user->email = $this->email;
        $user->password = Hash::make($pin);
        $user->save();

        if (isset(Auth::user()->company)) {
            $company = Auth::user()->company;
        }elseif (isset(Auth::user()->employee->company)) {
            $company = Auth::user()->employee->company;
        }

        Mail::to($this->email)->send(new AccountCreationMail($user, $company,$pin));

        $transporter = new Transporter;
        $transporter->creator_id = Auth::user()->id;
        $transporter->company_id = Auth::user()->employee->company->id;
        $transporter->user_id = $user->id;
        $transporter->name = $this->name;
        $transporter->transporter_number = $this->transporterNumber();
        $transporter->email = $this->email;
        $transporter->pin = $pin;
        $transporter->phonenumber = $this->phonenumber;
        $transporter->worknumber = $this->worknumber;
        $transporter->country = $this->country;
        $transporter->city = $this->city;
        $transporter->suburb = $this->suburb;
        $transporter->street_address = $this->street_address;
        $transporter->status = 1;
        $transporter->save();
        if (isset($this->cargo_id) && !empty($this->cargo_id)) {
            foreach ($this->cargo_id as $key => $value) {
                if (isset($this->cargo_id[$key])) {
                    $transporter->cargos()->attach($this->cargo_id[$key]);
                }
             
            }
            
        }
        if (isset($this->corridor_id) && !empty($this->corridor_id)) {
            foreach ($this->corridor_id as $key => $value) {
                if (isset($this->corridor_id[$key])) {
                    $transporter->corridors()->attach($this->corridor_id[$key]);
                }
             
            }
            
        }
       
       
       

        if (isset($this->contact_name)) {
            foreach ($this->contact_name as $key => $value) {
               $contact = new Contact;
               $contact->transporter_id = $transporter->id;
               $contact->category = 'transporter';
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
              $document->transporter_id = $transporter->id;
              $document->category = 'transporter';
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

        $this->dispatchBrowserEvent('hide-transporterModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Transporter Created Successfully!!"
        ]);

        return redirect(request()->header('Referer'));

        // }
        //     catch(\Exception $e){
        //     // Set Flash Message
        //     $this->dispatchBrowserEvent('alert',[
        //         'type'=>'error',
        //         'message'=>"Something went wrong while creating transporter!!"
        //     ]);
        // }
    }

    public function edit($id){
    $transporter = Transporter::find($id);
    $this->user_id = $transporter->user_id;
    $this->name = $transporter->name;
    $this->email = $transporter->email;
    $this->phonenumber = $transporter->phonenumber;
    $this->worknumber = $transporter->worknumber;
    $this->country = $transporter->country;
    $this->city = $transporter->city;
    $this->suburb = $transporter->suburb;
    $this->street_address = $transporter->street_address;
    $this->transporter_id = $transporter->id;
    $this->dispatchBrowserEvent('show-transporterEditModal');

    }

    public function update()
    {
        if ($this->transporter_id) {
            
            // try{
            $transporter = Transporter::find($this->transporter_id);
            $transporter->user_id = Auth::user()->id;
            $transporter->name = $this->name;
            $transporter->phonenumber = $this->phonenumber;
            $transporter->worknumber = $this->worknumber;
            $transporter->email = $this->email;
            $transporter->country = $this->country;
            $transporter->city = $this->city;
            $transporter->suburb = $this->suburb;
            $transporter->street_address = $this->street_address;
            $transporter->update();

            $this->dispatchBrowserEvent('hide-transporterEditModal');
            $this->resetInputFields();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Transporter Updated Successfully!!"
            ]);
            // }
            //     catch(\Exception $e){
            //     // Set Flash Message
            //     $this->dispatchBrowserEvent('alert',[
            //         'type'=>'error',
            //         'message'=>"Something went wrong while updating transporter!!"
            //     ]);
            // }

            return redirect(request()->header('Referer'));

        }
    }
    public function render()
    {
        $this->transporters = Transporter::latest()->get();
        return view('livewire.transporters.index',[
            'transporters'=>$this->transporters
        ]);
    }
}
