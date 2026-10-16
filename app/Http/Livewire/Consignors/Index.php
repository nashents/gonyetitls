<?php

namespace App\Http\Livewire\Consignors;

use Carbon\Carbon;
use App\Models\Contact;
use Livewire\Component;
use App\Models\Document;
use App\Models\Consignor;
use Livewire\WithPagination;
use Maatwebsite\Excel\Excel;
use Livewire\WithFileUploads;
use App\Exports\ConsignorsExport;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class Index extends Component
{
    use WithFileUploads;
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    private $consignors;
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
    public $vat_number;
    public $tin_number;

    public $search;
    protected $queryString = ['search'];

    public $consignor_id;
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

    

        
    public function exportConsignorsCSV(Excel $excel){

        return $excel->download(new ConsignorsExport, 'consignors.csv', Excel::CSV);
    }
    public function exportConsignorsPDF(Excel $excel){

        return $excel->download(new ConsignorsExport, 'consignors.pdf', Excel::DOMPDF);
    }
    public function exportConsignorsExcel(Excel $excel){
        return $excel->download(new ConsignorsExport, 'consignors.xlsx');
    }



    public function mount(){
        $this->resetPage();
    }

    public function consignorNumber(){
       
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

            $consignor = Consignor::orderBy('id', 'desc')->first();

        if (!$consignor) {
            $consignor_number =  $initials .'CR'. str_pad(1, 5, "0", STR_PAD_LEFT);
        }else {
            $number = $consignor->id + 1;
            $consignor_number =  $initials .'CR'. str_pad($number, 5, "0", STR_PAD_LEFT);
        }

        return  $consignor_number;


    }

    private function resetInputFields(){
        $this->consignors = "";
        $this->contact_name = "";
        $this->contact_surname = "";
        $this->contact_email = "";
        $this->contact_phonenumber = "";
        $this->department = "";
        $this->name = "";
        $this->phonenumber = "";
        $this->worknumber = "";
        $this->vat_number = "";
        $this->tin_number = "";
        $this->email = "";
        $this->title = "";
        $this->file = "";
        $this->expires_at = "";
        $this->country = "";
        $this->city = "";
        $this->suburb = "";
        $this->street_address = "";
    }
    public function updated($value){
        $this->validateOnly($value);
    }
    protected $rules = [
        'name' => 'required|unique:consignors,name,NULL,id,deleted_at,NULL|string|min:2',
   

    ];

   


    public function store(){
    
        DB::transaction(function () {

        $consignor = new Consignor;
        $consignor->company_id = Auth::user()->employee->company->id;
        $consignor->user_id = Auth::user()->id;
        $consignor->name = $this->name;
        $consignor->consignor_number = $this->consignorNumber();
        $consignor->email = $this->email;
        $consignor->phonenumber = $this->phonenumber;
        $consignor->worknumber = $this->worknumber;
        $consignor->country = $this->country;
        $consignor->vat_number = $this->vat_number;
        $consignor->tin_number = $this->tin_number;
        $consignor->city = $this->city;
        $consignor->suburb = $this->suburb;
        $consignor->street_address = $this->street_address;
        $consignor->status = 1;
        $consignor->save();

        if (isset($this->contact_name)) {
            foreach ($this->contact_name as $key => $value) {
               $contact = new Contact;
               $contact->consignor_id = $consignor->id;
               $contact->category = 'consignor';
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
              $document->consignor_id = $consignor->id;
              $document->category = 'consignor';
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

        $this->dispatchBrowserEvent('hide-consignorModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Consignor Created Successfully!!"
        ]);

    
    });

    }

    public function edit($id){
    $consignor = Consignor::find($id);
    $this->user_id = $consignor->user_id;
    $this->name = $consignor->name;
    $this->email = $consignor->email;
    $this->phonenumber = $consignor->phonenumber;
    $this->worknumber = $consignor->worknumber;
    $this->country = $consignor->country;
    $this->tin_number = $consignor->tin_number;
    $this->vat_number = $consignor->vat_number;
    $this->city = $consignor->city;
    $this->suburb = $consignor->suburb;
    $this->street_address = $consignor->street_address;
    $this->consignor_id = $consignor->id;
    $this->dispatchBrowserEvent('show-consignorEditModal');

    }

    public function update()
    {
         DB::transaction(function () {
        if ($this->consignor_id) {
            
            $consignor = Consignor::find($this->consignor_id);
            $consignor->user_id = Auth::user()->id;
            $consignor->name = $this->name;
            $consignor->phonenumber = $this->phonenumber;
            $consignor->worknumber = $this->worknumber;
            $consignor->email = $this->email;
            $consignor->vat_number = $this->vat_number;
            $consignor->tin_number = $this->tin_number;
            $consignor->country = $this->country;
            $consignor->city = $this->city;
            $consignor->suburb = $this->suburb;
            $consignor->street_address = $this->street_address;
            $consignor->update();

            $this->dispatchBrowserEvent('hide-consignorEditModal');
            $this->resetInputFields();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Consignor Updated Successfully!!"
            ]);
          
        }

    });
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        
        
                 
                 if (filled($this->search)) {
                    
                     return view('livewire.consignors.index',[
                         'consignors' => Consignor::query()
                         ->where('consignor_number','like', '%'.$this->search.'%')
                         ->orWhere('name','like', '%'.$this->search.'%')
                         ->orWhere('email','like', '%'.$this->search.'%')
                         ->orderBy('name','asc')->paginate(10),
                     ]);
                 }
                 else {
                    
                     return view('livewire.consignors.index',[
                        'consignors' => Consignor::query()->orderBy('name','asc')->paginate(10),
                        
                     ]);
                   
                 }
   
 
       
    }
}
