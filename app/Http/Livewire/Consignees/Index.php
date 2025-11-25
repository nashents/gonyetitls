<?php

namespace App\Http\Livewire\Consignees;

use Carbon\Carbon;
use App\Models\Contact;
use Livewire\Component;
use App\Models\Document;
use App\Models\Consignee;
use Livewire\WithPagination;
use Maatwebsite\Excel\Excel;
use Livewire\WithFileUploads;
use App\Exports\ConsigneesExport;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class Index extends Component
{
    use WithFileUploads;
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    private $consignees;
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

    public $consignee_id;
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

    

        
    public function exportConsigneesCSV(Excel $excel){

        return $excel->download(new ConsigneesExport, 'consignees.csv', Excel::CSV);
    }
    public function exportConsigneesPDF(Excel $excel){

        return $excel->download(new ConsigneesExport, 'consignees.pdf', Excel::DOMPDF);
    }
    public function exportConsigneesExcel(Excel $excel){
        return $excel->download(new ConsigneesExport, 'consignees.xlsx');
    }



    public function mount(){
        $this->resetPage();
    }

    public function consigneeNumber(){
       
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

            $consignee = Consignee::orderBy('id', 'desc')->first();

        if (!$consignee) {
            $consignee_number =  $initials .'C'. str_pad(1, 5, "0", STR_PAD_LEFT);
        }else {
            $number = $consignee->id + 1;
            $consignee_number =  $initials .'C'. str_pad($number, 5, "0", STR_PAD_LEFT);
        }

        return  $consignee_number;


    }

    private function resetInputFields(){
        $this->consignees = "";
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
        'name' => 'required|unique:consignees,name,NULL,id,deleted_at,NULL|string|min:2',
   

    ];

   


    public function store(){
    
        DB::transaction(function () {

        $consignee = new Consignee;
        $consignee->company_id = Auth::user()->employee->company->id;
        $consignee->user_id = Auth::user()->id;
        $consignee->name = $this->name;
        $consignee->consignee_number = $this->consigneeNumber();
        $consignee->email = $this->email;
        $consignee->phonenumber = $this->phonenumber;
        $consignee->worknumber = $this->worknumber;
        $consignee->country = $this->country;
        $consignee->vat_number = $this->vat_number;
        $consignee->tin_number = $this->tin_number;
        $consignee->city = $this->city;
        $consignee->suburb = $this->suburb;
        $consignee->street_address = $this->street_address;
        $consignee->status = 1;
        $consignee->save();

        if (isset($this->contact_name)) {
            foreach ($this->contact_name as $key => $value) {
               $contact = new Contact;
               $contact->consignee_id = $consignee->id;
               $contact->category = 'consignee';
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
              $document->consignee_id = $consignee->id;
              $document->category = 'consignee';
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

        $this->dispatchBrowserEvent('hide-consigneeModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Consignee Created Successfully!!"
        ]);

    
    });

    }

    public function edit($id){
    $consignee = Consignee::find($id);
    $this->user_id = $consignee->user_id;
    $this->name = $consignee->name;
    $this->email = $consignee->email;
    $this->phonenumber = $consignee->phonenumber;
    $this->worknumber = $consignee->worknumber;
    $this->country = $consignee->country;
    $this->tin_number = $consignee->tin_number;
    $this->vat_number = $consignee->vat_number;
    $this->city = $consignee->city;
    $this->suburb = $consignee->suburb;
    $this->street_address = $consignee->street_address;
    $this->consignee_id = $consignee->id;
    $this->dispatchBrowserEvent('show-consigneeEditModal');

    }

    public function update()
    {
         DB::transaction(function () {
        if ($this->consignee_id) {
            
            $consignee = Consignee::find($this->consignee_id);
            $consignee->user_id = Auth::user()->id;
            $consignee->name = $this->name;
            $consignee->phonenumber = $this->phonenumber;
            $consignee->worknumber = $this->worknumber;
            $consignee->email = $this->email;
            $consignee->vat_number = $this->vat_number;
            $consignee->tin_number = $this->tin_number;
            $consignee->country = $this->country;
            $consignee->city = $this->city;
            $consignee->suburb = $this->suburb;
            $consignee->street_address = $this->street_address;
            $consignee->update();

            $this->dispatchBrowserEvent('hide-consigneeEditModal');
            $this->resetInputFields();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Consignee Updated Successfully!!"
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
                    
                     return view('livewire.consignees.index',[
                         'consignees' => Consignee::query()
                         ->where('consignee_number','like', '%'.$this->search.'%')
                         ->orWhere('name','like', '%'.$this->search.'%')
                         ->orWhere('email','like', '%'.$this->search.'%')
                         ->orderBy('name','asc')->paginate(10),
                     ]);
                 }
                 else {
                    
                     return view('livewire.consignees.index',[
                        'consignees' => Consignee::query()->orderBy('name','asc')->paginate(10),
                        
                     ]);
                   
                 }
   
 
       
    }
}
