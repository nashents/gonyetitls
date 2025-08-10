<?php

namespace App\Http\Livewire\GatePasses;

use Storage;
use App\Models\Gate;
use App\Models\Trip;
use App\Models\Group;
use App\Models\Horse;
use App\Models\Branch;
use App\Models\Driver;
use App\Models\Trailer;
use App\Models\Visitor;
use Livewire\Component;
use App\Models\Employee;
use App\Models\GatePass;
use Livewire\WithPagination;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class Index extends Component
{

    use WithPagination;


    protected $paginationTheme = 'bootstrap';
    public $search;
    protected $queryString = ['search'];
    public $from;
    public $to;

    public $gate_pass_filter;
    private $individual_gate_passes;
    private $trip_gate_passes;
    public $type;
    public $trips;
    public $trip_id;
    public $trailers;
    public $trailer_id;
    public $drivers;
    public $driver_id;
    public $horses;
    public $horse_id;
    public $employees;
    public $employee_id;
    public $groups;
    public $group_id;
    public $visitors;
    public $visitor_id;
    public $gates;
    public $gate_id;
    public $branches;
    public $selectedBranch;
    public $branch;
    public $entry;
    public $exit;
    public $reason;
    public $acknowledgement = False;
    public $signature;
    public $vrn;
    public $make;
    

    public $gate_name;
    public $group_name;
    public $name;
    public $surname;
    public $idnumber;
    public $phonenumber;

    public $trailer_inputs = [];
    public $t = 1;
    public $s = 1;

    protected $listeners = ['setSignatureData'];

    public function setSignatureData($signature)
    {
        $this->signature = $signature;
   
    }

    public function trailerAdd($t)
    {
        $t = $t + 1;
        $this->t = $t;
        array_push($this->trailer_inputs ,$t);
    }

    public function trailerRemove($t)
    {
        unset($this->trailer_inputs[$t]);
    }

    public function mount(){
        $this->gate_pass_filter = "created_at";
        $this->branches = Branch::latest()->get();
         $this->gates = Gate::latest()->get();
        $this->employees = Employee::orderBy('name','asc')->orderBy('surname','asc')->get();
        $this->visitors = Visitor::orderBy('created_at','desc')->get();
        $this->trips = Trip::latest()->whereYear('start_date',date('Y'))->get();
        $this->groups = Group::orderBy('created_at','desc')->get();
        $this->horses = Horse::orderBy('registration_number','asc')->get();
        $this->drivers = Driver::with('employee:id,name,surname')->latest()->get();
        $this->trailers = Trailer::latest()->get();
    }

    public function gate_passNumber(){

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
        $fuel = GatePass::orderBy('id', 'desc')->first();
        if(!$fuel){
        $gate_pass_number =  $initials .'GP'. str_pad(1, 5, "0", STR_PAD_LEFT);
        }else{
        $number = $fuel->id + 1 ;
        $gate_pass_number = $initials .'GP'. str_pad($number, 5, "0", STR_PAD_LEFT);
        }
        return $gate_pass_number;
    }

    // public function updated($value){
    //     $this->validateOnly($value);
    // }
    // // protected $rules = [
        
    // // ];


    public function updatedSelectedBranch($branch){
        if (!is_null($branch)) {
            $branch = Branch::find($branch);
            $this->branch = $branch;
          
        }
    }

    private function resetInputFields(){
        $this->selectedBranch = '';
        $this->gate_id = '';
        $this->group_id = '';
        $this->visitor_id = '';
        $this->employee_id = '';
        $this->reason = '';
        $this->exit = '';
        $this->make = '';
        $this->vrn = '';
        $this->entry = '';
        $this->acknowledgement = '';
        $this->signature = '';
    }
    private function resetVisitorInputFields(){
        $this->name = '';
        $this->surname = '';
        $this->idnumber = '';
        $this->phonenumber = '';
    }
  
    private function resetGroupInputFields(){
        $this->group_name = '';
    }
    private function resetGateInputFields(){
        $this->gate_name = '';
    }

        public function refresh($category){

        if($category == "gates"){
            $this->gates = Gate::latest()->get();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Gates Refreshed Successfully!!."
            ]);
        }
        elseif($category == "visitors"){
            $this->visitors = Visitor::latest()->get();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Visitors Refreshed Successfully!!."
            ]);
        } 
        elseif($category == "groups"){
            $this->groups = Group::latest()->get();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Groups Refreshed Successfully!!."
            ]);
        } 
    }

    public function storeGroup(){

        $this->validate([
           'group_name' => 'required|unique:groups,name,NULL,id,deleted_at,NULL|string',
        ]);


        $group = Group::firstOrNew([
            'name' => $this->group_name,
        ]);

        if (!$group->exists) {
            $group->user_id = Auth::id();
            $group->save();
        }

        $this->group_id = $group->id;
        $this->groups = Group::latest()->get();

        $this->dispatchBrowserEvent('hide-groupModal');
        $this->resetGroupInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Group Created Successfully!!"
        ]);

      
    }
    public function storeGate(){
    
        $this->validate([
           'gate_name' => 'required|unique:gates,name,NULL,id,deleted_at,NULL|string',
        ]);


        $gate = Gate::firstOrNew([
            'name' => $this->gate_name,
            'branch_id' => $this->selectedBranch,
        ]);

        if (!$gate->exists) {
            $gate->user_id = Auth::id();
            $gate->save();
        }

        $this->gate_id = $gate->id;

        $branch = Branch::find($this->selectedBranch);
         $this->gates = Gate::latest()->get();

        $this->dispatchBrowserEvent('hide-gateModal');
        $this->resetGateInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Gate Created Successfully!!"
        ]);

      
    
    }


    public function storeVisitor(){

        $this->validate([
           'name' => 'required|string',
           'surname' => 'required|string',
           'idnumber' => 'required|unique:visitors,name,NULL,id,deleted_at,NULL|string',
        ]);

        $visitor = Visitor::firstOrNew([
            'name' => $this->name,
            'surname' => $this->surname,
            'idnumber' => $this->idnumber,
        ]);

        // Always update editable fields
        $visitor->phonenumber = $this->phonenumber;
        $visitor->group_id = $this->group_id;

        // Only assign user_id on new record
        if (!$visitor->exists) {
            $visitor->user_id = Auth::id();
        }

        $visitor->save();
        $this->visitor_id = $visitor->id;

        $this->dispatchBrowserEvent('hide-visitorModal');
        $this->resetVisitorInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Visitor Created Successfully!!"
        ]);

     
    }

    
    public function store(){

     

         $this->validate([
           'entry' => 'required',
           'reason' => 'required',
           'visitor_id' => 'required',
           'employee_id' => 'required',
           'acknowledgement' => 'required',
        //    'signature' => 'required',
        ]);

        $gate_pass = new GatePass;
        $gate_pass->user_id = Auth::user()->id;
        $gate_pass->gate_pass_number = $this->gate_passNumber();
        $gate_pass->type = "Individual";
        $gate_pass->entry = $this->entry;
        $gate_pass->exit = $this->exit;
        $gate_pass->reason = $this->reason;
        $gate_pass->employee_id = $this->employee_id ? $this->employee_id : null;
        $gate_pass->gate_id = $this->gate_id ? $this->gate_id : null;
        $gate_pass->branch_id = $this->selectedBranch;
        $gate_pass->visitor_id = $this->visitor_id ? $this->visitor_id : null;
        $gate_pass->group_id = $this->group_id ? $this->group_id : null;
        $gate_pass->authorization = "approved";
        $gate_pass->acknowledgement = $this->acknowledgement;
        $gate_pass->vrn = $this->vrn;
        $gate_pass->make = $this->make;

        if ($this->signature) {

            $image = str_replace('data:image/png;base64,', '', $this->signature);
            $image = str_replace(' ', '+', $image);
            $imageData = base64_decode($image);

            // Save to a temp file
            $tmpFilePath = sys_get_temp_dir() . '/' . uniqid() . '.png';
            file_put_contents($tmpFilePath, $imageData);

            // Wrap as UploadedFile
            $file = new UploadedFile(
                $tmpFilePath,
                uniqid() . '.png',
                'image/png',
                null,
                true
            );

            // Store like normal
            $fileNameToStore = uniqid() . '.png';
            $file->storeAs('/uploads', $fileNameToStore, 'path');

            $gate_pass->signature = $fileNameToStore;
           
        }
       
        $gate_pass->save();
       
        $this->dispatchBrowserEvent('hide-gate_passModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Gatepass Created Successfully!!"
        ]);
    }


    public function edit($id){
        $gate_pass = GatePass::find($id);
        $this->entry = $gate_pass->entry;
        $this->exit = $gate_pass->exit;
        $this->trip_id = $gate_pass->trip_id;
        $trip = Trip::find($gate_pass->trip_id);
        if($trip){
            $this->horse_id = $trip->horse_id;
            $this->driver_id = $trip->driver_id;
        }
        $trailers = $gate_pass->trailers;
        foreach($trailers as $trailer){
            $this->trailer_id[] = $trailer->id;
        }
        $this->selectedBranch = $gate_pass->branch_id;
        $this->gate_id = $gate_pass->gate_id;
        $this->reason = $gate_pass->reason;
        $this->authorization = $gate_pass->authorization;
        $this->visitor_id = $gate_pass->visitor_id;
        $this->group_id = $gate_pass->group_id;
        $this->type = $gate_pass->type;
        $this->employee_id = $gate_pass->employee_id;
        $this->gate_pass_id = $gate_pass->id;
        $this->branch = $gate_pass->branch;
        $this->vrn = $gate_pass->vrn;
        $this->make = $gate_pass->make;
        $this->gates = Gate::latest()->get();
    
        $this->dispatchBrowserEvent('show-gate_passEditModal');
    }


    public function update(){
        $gate_pass = GatePass::find($this->gate_pass_id);
        $gate_pass->user_id = Auth::user()->id;
        $gate_pass->type = $this->type;
        $gate_pass->entry = $this->entry;
        $gate_pass->exit = $this->exit;
        $gate_pass->reason = $this->reason;
        $gate_pass->employee_id = $this->employee_id;
        $gate_pass->gate_id = $this->gate_id;
        $gate_pass->branch_id = $this->selectedBranch;
        $gate_pass->visitor_id = $this->visitor_id;
        $gate_pass->group_id = $this->group_id;
        $gate_pass->vrn = $this->vrn;
        $gate_pass->make = $this->make;
        $gate_pass->update();

        $this->dispatchBrowserEvent('hide-gate_passEditModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Gatepass Updated Successfully!!"
        ]);
    }

    public function render()
    {
        $this->visitors = Visitor::latest()->get();
        $this->groups = Group::latest()->get();

        if (isset($this->from) && isset($this->to)) {
            if (filled($this->search)) {
                 return view('livewire.gate-passes.index',[
                'individual_gate_passes' => GatePass::with('branch:id,name')
                ->whereBetween('created_at',[$this->from, $this->to])
                ->where('type','Individual')
                ->where(function ($query) {
                            $query->where('gate_pass_number','like', '%'.$this->search.'%')
                                    ->orWhereHas('visitor', function ($q) {
                                        $q->where('name', 'like', '%'.$this->search.'%')
                                        ->orWhere('surname', 'like', '%'.$this->search.'%')
                                        ->orWhere('phonenumber', 'like', '%'.$this->search.'%')
                                        ->orWhere('idnumber', 'like', '%'.$this->search.'%');
                                        })
                                    ->orWhereHas('employee', function ($q) {
                                        $q->where(DB::raw("concat(name, ' ', surname)"), 'LIKE', "%".$this->search."%");
                                        })
                                    ->orWhereHas('gate', function ($q) {
                                        $q->where('name', 'like', '%'.$this->search.'%');
                                        })
                                    ->orWhereHas('group', function ($q) {
                                        $q->where('name', 'like', '%'.$this->search.'%');
                                        })
                                    ->orWhereHas('branch', function ($q) {
                                        $q->where('name', 'like', '%'.$this->search.'%');
                                        })
                                    ->orWhere('vrn','like', '%'.$this->search.'%')
                                    ->orWhere('make','like', '%'.$this->search.'%')
                                    ->orWhere('exit','like', '%'.$this->search.'%')
                                    ->orWhere('reason','like', '%'.$this->search.'%')
                                    ->orWhere('entry','like', '%'.$this->search.'%');
                    })
                ->orderBy($this->gate_pass_filter,'desc')->paginate(10),
            ]);
            }else{
                 return view('livewire.gate-passes.index',[
                'trip_gate_passes' => GatePass::with('trip','horse','driver','branch:id,name')
                ->whereBetween('created_at',[$this->from, $this->to])
                ->where('type','Trip')
                ->orderBy($this->gate_pass_filter,'desc')->paginate(10),
                'individual_gate_passes' => GatePass::with('branch:id,name')
                ->whereBetween('created_at',[$this->from, $this->to])
                ->where('type','Individual')
                ->orderBy($this->gate_pass_filter,'desc')->paginate(10),
            ]);
            }
        }elseif (filled($this->search)) {
             return view('livewire.gate-passes.index',[
                'individual_gate_passes' => GatePass::with('branch:id,name')
                ->whereMonth('created_at',date('m'))
                ->whereYear('created_at',date('Y'))
                ->where('type','Individual')
                ->where(function ($query) {
                    $query->where('gate_pass_number','like', '%'.$this->search.'%')
                            ->orWhereHas('visitor', function ($q) {
                                $q->where('name', 'like', '%'.$this->search.'%')
                                ->orWhere('surname', 'like', '%'.$this->search.'%')
                                ->orWhere('phonenumber', 'like', '%'.$this->search.'%')
                                ->orWhere('idnumber', 'like', '%'.$this->search.'%');
                                })
                            ->orWhereHas('employee', function ($q) {
                                $q->where(DB::raw("concat(name, ' ', surname)"), 'LIKE', "%".$this->search."%");
                                })
                            ->orWhereHas('gate', function ($q) {
                                $q->where('name', 'like', '%'.$this->search.'%');
                                })
                            ->orWhereHas('group', function ($q) {
                                $q->where('name', 'like', '%'.$this->search.'%');
                                })
                            ->orWhereHas('branch', function ($q) {
                                $q->where('name', 'like', '%'.$this->search.'%');
                                })
                            ->orWhere('vrn','like', '%'.$this->search.'%')
                            ->orWhere('make','like', '%'.$this->search.'%')
                            ->orWhere('exit','like', '%'.$this->search.'%')
                            ->orWhere('reason','like', '%'.$this->search.'%')
                            ->orWhere('entry','like', '%'.$this->search.'%');
                })
                ->orderBy($this->gate_pass_filter,'desc')->paginate(10),
            ]);
        }else{
            return view('livewire.gate-passes.index',[
                'trip_gate_passes' => GatePass::with('trip','horse','driver','branch:id,name')
                ->whereMonth('created_at',date('m'))
                ->whereYear('created_at',date('Y'))
                ->where('type','Trip')
                ->orderBy($this->gate_pass_filter,'desc')->paginate(10),
                'individual_gate_passes' => GatePass::with('branch:id,name')
                ->whereMonth('created_at',date('m'))
                ->whereYear('created_at',date('Y'))
                ->where('type','Individual')->orderBy($this->gate_pass_filter,'desc')->paginate(10),
               
            ]);
        }
        
       
    }
}  
