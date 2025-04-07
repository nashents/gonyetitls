<?php

namespace App\Http\Livewire\GatePasses;

use Livewire\Component;
use App\Models\GatePass;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

class Approved extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';
    public $search;
    protected $queryString = ['search'];
    public $from;
    public $to;

    private $individual_gate_passes;
    private $trip_gate_passes;
    public $gate_pass_id;
    public $authorize;
    public $comments;
    public $gate_pass;
    public $department;

    public function mount($department){
        $this->department = $department;

    }

    public function authorize($id){
        $gate_pass = GatePass::find($id);
        $this->gate_pass_id = $gate_pass->id;
        $this->gate_pass = $gate_pass;
        $this->dispatchBrowserEvent('show-authorizationModal');
      }

      public function update(){
        try{
            if (isset($this->department)) {
                if ($this->department == "logistics") {
                     $gate_pass = GatePass::find($this->gate_pass_id);
                     $gate_pass->logistics_authorized_by_id = Auth::user()->id;
                     $gate_pass->logistics_authorization = $this->authorize;
                     $gate_pass->logistics_authorization_reason = $this->comments;
                     $gate_pass->update();
    
                     if ($this->authorize == "approved") {
                        $this->dispatchBrowserEvent('hide-authorizationModal');
                        $this->dispatchBrowserEvent('alert',[
                            'type'=>'success',
                            'message'=>"Gate Pass Approved Successfully"
                        ]);
                        return redirect()->route('gate_passes.approved',['department'=>'logistics']);
                    }else {
                        $this->dispatchBrowserEvent('hide-authorizationModal');
                        $this->dispatchBrowserEvent('alert',[
                            'type'=>'success',
                            'message'=>"Gate Pass Rejected Successfully"
                        ]);
                        return redirect()->route('gate_passes.rejected',['department'=>'logistics']);
                    }
                }elseif ($this->department == "workshop") {
                     $gate_pass = GatePass::find($this->gate_pass_id);
                     $gate_pass->workshop_authorized_by_id = Auth::user()->id;
                     $gate_pass->workshop_authorization = $this->authorize;
                     $gate_pass->workshop_authorization_reason = $this->comments;
                     $gate_pass->update();
    
                     if ($this->authorize == "approved") {
                        $this->dispatchBrowserEvent('hide-authorizationModal');
                        $this->dispatchBrowserEvent('alert',[
                            'type'=>'success',
                            'message'=>"Gate Pass Approved Successfully"
                        ]);
                        return redirect()->route('gate_passes.approved',['department'=>'workshop']);
                    }else {
                        $this->dispatchBrowserEvent('hide-authorizationModal');
                        $this->dispatchBrowserEvent('alert',[
                            'type'=>'success',
                            'message'=>"Gate Pass Rejected Successfully"
                        ]);
                        return redirect()->route('gate_passes.rejected',['department'=>'workshop']);
                    }
                }elseif ($this->department == "security") {
                     $gate_pass = GatePass::find($this->gate_pass_id);
                     $gate_pass->authorized_by_id = Auth::user()->id;
                     $gate_pass->authorization = $this->authorize;
                     $gate_pass->authorization_reason = $this->comments;
                     $gate_pass->update();
    
                     if ($this->authorize == "approved") {
                        $this->dispatchBrowserEvent('hide-authorizationModal');
                        $this->dispatchBrowserEvent('alert',[
                            'type'=>'success',
                            'message'=>"Gate Pass Approved Successfully"
                        ]);
                        return redirect()->route('gate_passes.approved',['department'=>'security']);
                    }else {
                        $this->dispatchBrowserEvent('hide-authorizationModal');
                        $this->dispatchBrowserEvent('alert',[
                            'type'=>'success',
                            'message'=>"Gate Pass Rejected Successfully"
                        ]);
                        return redirect()->route('gate_passes.rejected',['department'=>'security']);
                    }
                }
             }
    
        
        }
        catch(\Exception $e){
            $this->dispatchBrowserEvent('hide-authorizationModal');
            $this->dispatchBrowserEvent('alert',[
                'type'=>'error',
                'message'=>"Something went wrong while trying to authorize Gate Pass!!"
            ]);
            }
    
      }
    public function render()
    {
        if ($this->department == "logistics") {
            return view('livewire.gate-passes.approved',[
                'trip_gate_passes' => GatePass::with('trip:id,trip_number','horse:id,registration_number','driver','driver.employee:id,name,surname','branch:id,name')->where('logistics_authorization', 'approved')->where('type','Trip')->orderBy('gate_pass_number','desc')->take(100)->paginate(10),
                'department' => $this->department
            ]);
        }elseif($this->department == "workshop"){
            return view('livewire.gate-passes.approved',[
                'trip_gate_passes' => GatePass::with('trip:id,trip_number','horse:id,registration_number','driver','driver.employee:id,name,surname','branch:id,name')->where('workshop_authorization', 'approved')->where('type','Trip')->orderBy('gate_pass_number','desc')->take(100)->paginate(10),
                'department' => $this->department
            ]);
        }elseif($this->department == "security"){
            return view('livewire.gate-passes.approved',[
                'trip_gate_passes' => GatePass::with('trip:id,trip_number','horse:id,registration_number','driver','driver.employee:id,name,surname','branch:id,name')->where('authorization', 'approved')->where('type','Trip')->orderBy('gate_pass_number','desc')->take(100)->paginate(10),
                'individual_gate_passes' => GatePass::with('trip:id,trip_number','horse:id,registration_number','driver','driver.employee:id,name,surname','branch:id,name')->where('authorization', 'approved')->where('type','Individual')->orderBy('gate_pass_number','desc')->take(100)->paginate(10),
                'department' => $this->department
            ]);
        }
    }
}
