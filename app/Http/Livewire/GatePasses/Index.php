<?php

namespace App\Http\Livewire\GatePasses;

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
use Illuminate\Support\Facades\Auth;

class Index extends Component
{

    use WithPagination;

    protected $paginationTheme = 'bootstrap';
    public $search;
    protected $queryString = ['search'];
    public $from;
    public $to;

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
    public $invited_by_id;

    public $gate_name;
    public $group_name;
    public $name;
    public $surname;
    public $idnumber;
    public $phonenumber;

    public $trailer_inputs = [];
    public $t = 1;
    public $s = 1;

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
      
        $this->branches = Branch::latest()->get();
        $this->gates = collect();
        $this->employees = Employee::latest()->get();
        $this->visitors = Visitor::latest()->get();
        $this->trips = Trip::latest()->whereYear('start_date',date('Y'))->get();
        $this->groups = Group::latest()->get();
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

    public function updated($value){
        $this->validateOnly($value);
    }
    protected $rules = [
        'gate_name' => 'required|unique:gates,name,NULL,id,deleted_at,NULL|string',
    ];


    public function updatedSelectedBranch($branch){
        if (!is_null($branch)) {
            $branch = Branch::find($branch);
            $this->branch = $branch;
            $this->gates = $branch->gates;
        }
    }

    private function resetInputFields(){
        $this->group_name = '';
        $this->name = '';
        $this->surname = '';
        $this->idnumber = '';
        $this->phonenumber = '';
        $this->reason = '';
        $this->exit = '';
        $this->entry = '';
        $this->selectedBranch = '';
        $this->gate_id = '';
        $this->type = '';
    }

    public function storeGroup(){

        try{

        $group = new Group;
        $group->name = $this->group_name;
        $group->save();
        $this->group_id = $group->id;

        $this->dispatchBrowserEvent('hide-groupModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Group Created Successfully!!"
        ]);

        }
        catch(\Exception $e){
        // Set Flash Message
        $this->dispatchBrowserEvent('alert',[
            'type'=>'error',
            'message'=>"Something goes wrong while creating group!!"
        ]);
    }
    }
    public function storeGate(){

        try{

        $gate = new Gate;
        $gate->user_id = Auth::user()->id;
        $gate->branch_id = $this->selectedBranch;
        $gate->name = $this->gate_name;
        $gate->save();
        $this->gate_id = $gate->id;

        $this->dispatchBrowserEvent('hide-gateModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Gate Created Successfully!!"
        ]);

        }
        catch(\Exception $e){
        // Set Flash Message
        $this->dispatchBrowserEvent('alert',[
            'type'=>'error',
            'message'=>"Something goes wrong while creating group!!"
        ]);
    }
    }


    public function storeVisitor(){

        try{

        $visitor = new Visitor;
        $visitor->user_id = Auth::user()->id;
        $visitor->group_id = $this->group_id;
        $visitor->name = $this->name;
        $visitor->surname = $this->surname;
        $visitor->idnumber = $this->idnumber;
        $visitor->phonenumber = $this->phonenumber;
        $visitor->save();
        $this->visitor_id = $visitor->id;

        $this->dispatchBrowserEvent('hide-visitorModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Visitor Created Successfully!!"
        ]);

        }
        catch(\Exception $e){
        // Set Flash Message
        $this->dispatchBrowserEvent('alert',[
            'type'=>'error',
            'message'=>"Something goes wrong while creating visitor!!"
        ]);
    }
    }
    public function store(){
        $gate_pass = new GatePass;
        $gate_pass->user_id = Auth::user()->id;
        $gate_pass->gate_pass_number = $this->gate_passNumber();
        $gate_pass->type = "Individual";
        $gate_pass->entry = $this->entry;
        $gate_pass->exit = $this->exit;
        $gate_pass->reason = $this->reason;
        $gate_pass->invited_by_id = $this->invited_by_id ? $this->invited_by_id : null;
        $gate_pass->gate_id = $this->gate_id ? $this->gate_id : null;
        $gate_pass->branch_id = $this->selectedBranch;
        $gate_pass->visitor_id = $this->visitor_id ? $this->visitor_id : null;
        $gate_pass->group_id = $this->group_id ? $this->group_id : null;
        $gate_pass->authorization = "approved";
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
        $this->invited_by_id = $gate_pass->invited_by_id;
        $this->gate_pass_id = $gate_pass->id;
        $this->branch = $gate_pass->branch;
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
        $gate_pass->invited_by_id = $this->invited_by_id;
        $gate_pass->gate_id = $this->gate_id;
        $gate_pass->branch_id = $this->selectedBranch;
        $gate_pass->visitor_id = $this->visitor_id;
        $gate_pass->group_id = $this->group_id;
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
        
        return view('livewire.gate-passes.index',[
            'trip_gate_passes' => GatePass::with('trip','horse','driver','branch:id,name')->where('type','Trip')->orderBy('gate_pass_number','desc')->take(100)->paginate(10),
            'individual_gate_passes' => GatePass::with('branch:id,name')->where('type','Individual')->orderBy('gate_pass_number','desc')->take(100)->paginate(10),
            'gates' => Gate::all(),
            'gate_id' => $this->gate_id,
            'groups' => $this->groups,
            'group_id' => $this->group_id,
            'visitors' => $this->visitors,
            'visitor_id' => $this->visitor_id,
        ]);
    }
}
