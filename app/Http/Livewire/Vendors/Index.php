<?php

namespace App\Http\Livewire\Vendors;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Vendor;
use App\Models\Contact;
use Livewire\Component;
use App\Models\Currency;
use App\Models\Document;
use App\Models\VendorType;
use Maatwebsite\Excel\Excel;
use Livewire\WithFileUploads;
use App\Exports\VendorsExport;
use App\Mail\AccountCreationMail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;

class Index extends Component
{
    use WithFileUploads;

    public $vendors;
    public $vendor_types;
    public $contact_name;
    public $contact_surname;
    public $contact_email;
    public $contact_phonenumber;
    public $name;
    public $vat_number;
    public $tin_number;
    public $phonenumber;
    public $worknumber;
    public $email;
    public $country;
    public $city;
    public $currencies;
    public $currency_id;
    public $website;
    public $suburb;
    public $street_address;

    public $vendor_id;
    public $vendor_type_id;
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

        
    public function exportVendorsCSV(Excel $excel){

        return $excel->download(new VendorsExport, 'vendors.csv', Excel::CSV);
    }
    public function exportVendorsPDF(Excel $excel){

        return $excel->download(new VendorsExport, 'vendors.pdf', Excel::DOMPDF);
    }
    public function exportVendorsExcel(Excel $excel){
        return $excel->download(new VendorsExport, 'vendors.xlsx');
    }


    public function vendorNumber(){

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

            $last_vendor_id = Vendor::latest()->pluck('id')->first();

        if (!$last_vendor_id) {
            $vendor_number =  $initials .'V'. str_pad(1, 5, "0", STR_PAD_LEFT);
        }else {
            $vendor_number = $last_vendor_id + 1;
            $vendor_number =  $initials .'V'. str_pad($vendor_number, 5, "0", STR_PAD_LEFT);
        }

        return  $vendor_number;


    }

    public function mount(){
        $this->vendors = Vendor::latest()->get();
        $this->currencies = Currency::orderBy('name','asc')->get();
        $this->vendor_types = VendorType::orderBy('name','asc')->get();
    }

    public function updated($value){
        $this->validateOnly($value);
    }

      protected $rules = [
        'name' => 'required|unique:vendors,name,NULL,id,deleted_at,NULL|string|min:2',
    ];
    private function resetInputFields(){
        $this->contact_name = "";
        $this->contact_surname = "";
        $this->contact_email = "";
        $this->contact_phonenumber = "";
        $this->name = "";
        $this->currency_id = "";
        $this->phonenumber = "";
        $this->worknumber = "";
        $this->email = "";
        $this->country = "";
        $this->website = "";
        $this->city = "";
        $this->suburb = "";
        $this->street_address = "";
        $this->vat_number = "";
        $this->tin_number = "";
        $this->vendor_type_id = "";
        $this->title = "";
        $this->file = "";
        $this->expires_at = "";
    }
  

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
            $user->category = 'vendor';
            $user->email = $this->email;
            $user->password = Hash::make($pin);
            $user->save();
    
            if (isset(Auth::user()->company)) {
                $company = Auth::user()->company;
            }elseif (isset(Auth::user()->employee->company)) {
                $company = Auth::user()->employee->company;
            }
    
        Mail::to($this->email)->send(new AccountCreationMail($user, $company,$pin));

        $vendor = new Vendor;
        $vendor->user_id = $user->id;
        $vendor->creator_id = Auth::user()->id;
        $vendor->company_id = Auth::user()->employee->company->id;
        $vendor->vendor_number = $this->vendorNumber();
        $vendor->vendor_type_id = $this->vendor_type_id;
        $vendor->name = $this->name;
        $vendor->email = $this->email;
        $vendor->pin = $pin;
        $vendor->phonenumber = $this->phonenumber;
        $vendor->currency_id = $this->currency_id ? $this->currency_id : NULL;
        $vendor->worknumber = $this->worknumber;
        $vendor->website = $this->website;
        $vendor->country = $this->country;
        $vendor->vat_number = $this->vat_number;
        $vendor->tin_number = $this->tin_number;
        $vendor->city = $this->city;
        $vendor->suburb = $this->suburb;
        $vendor->street_address = $this->street_address;
        $vendor->status = 1;
        $vendor->save();

        if (isset($this->contact_name)) {
            foreach ($this->contact_name as $key => $value) {
               $contact = new Contact;
               $contact->vendor_id = $vendor->id;
               $contact->category = 'vendor';
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
              $document->vendor_id = $vendor->id;
              $document->category = 'vendor';
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

        $this->dispatchBrowserEvent('hide-vendorModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Vendor Created Successfully!!"
        ]);

        }catch(\Exception $e){
            // Set Flash Message
            $this->dispatchBrowserEvent('hide-vendorModal');
            $this->dispatchBrowserEvent('alert',[

                'type'=>'error',
                'message'=>"Something went wrong while creating vendor!!"
            ]);
        }
    }

    public function edit($id){
    $vendor = Vendor::find($id);
    $this->vendor_type_id = $vendor->vendor_type_id;
    $this->name = $vendor->name;
    $this->contact_name = $vendor->contact_name;
    $this->contact_surname = $vendor->contact_surname;
    $this->email = $vendor->email;
    $this->phonenumber = $vendor->phonenumber;
    $this->worknumber = $vendor->worknumber;
    $this->website = $vendor->website;
    $this->vat_number = $vendor->vat_number;
    $this->tin_number = $vendor->tin_number;
    $this->currency_id = $vendor->currency_id;
    $this->country = $vendor->country;
    $this->city = $vendor->city;
    $this->suburb = $vendor->suburb;
    $this->street_address = $vendor->street_address;
    $this->vendor_id = $vendor->id;
    $this->dispatchBrowserEvent('show-vendorEditModal');

    }

    public function update()
    {
        if ($this->vendor_id) {
            try{
            $vendor = Vendor::find($this->vendor_id);
            $vendor->vendor_type_id = $this->vendor_type_id;
            $vendor->currency_id = $this->currency_id;
            $vendor->name = $this->name;
            $vendor->phonenumber = $this->phonenumber;
            $vendor->worknumber = $this->worknumber;
            $vendor->email = $this->email;
            $vendor->vat_number = $this->vat_number;
            $vendor->tin_number = $this->tin_number;
            $vendor->country = $this->country;
            $vendor->city = $this->city;
            $vendor->suburb = $this->suburb;
            $vendor->street_address = $this->street_address;
            $vendor->update();

            $this->dispatchBrowserEvent('hide-vendorEditModal');
            $this->resetInputFields();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Vendor Updated Successfully!!"
            ]);
            }catch(\Exception $e){
                // Set Flash Message
                $this->dispatchBrowserEvent('hide-vendorEditModal');
                $this->dispatchBrowserEvent('alert',[

                    'type'=>'error',
                    'message'=>"Something went wrong while updating vendor!!"
                ]);
            }
        }
    }
    public function render()
    {  
        $this->vendor_types = VendorType::orderBy('name','asc')->get();
        $this->vendors = Vendor::latest()->get();
        return view('livewire.vendors.index',[
            'vendors' => $this->vendors,
            'vendor_types' => $this->vendor_types
        ]);
    }
}
