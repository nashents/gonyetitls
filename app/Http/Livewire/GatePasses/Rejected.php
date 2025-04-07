<?php

namespace App\Http\Livewire\GatePasses;

use Livewire\Component;
use App\Models\GatePass;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

class Rejected extends Component
{
    
    use WithPagination;

    protected $paginationTheme = 'bootstrap';
    public $search;
    protected $queryString = ['search'];
    public $from;
    public $to;


    // private $trip_gate_passes;
    public $gate_pass_id;
    public $authorize;
    public $comments;
    public $gate_pass;
    public $department;

    public $selectedRows = [];
    public $selectPageRows = false;

    public function mount($department){
        $this->department = $department;

    }

    public function showBulkyAuthorize(){
        $this->dispatchBrowserEvent('show-bulkyAuthorizationModal');
      }

    public function updatedSelectPageRows($value){

        if ($value) {
            $this->selectedRows = $this->trip_gate_passes->pluck('id')->map(function ($id){
                return (string) $id;
            });
        }else {
            $this->reset(['selectedRows','selectPageRows']);
        }
     
      }

      public function getTripGatePassesProperty(){

        if ($this->department == "logistics") {
            return  GatePass::with('trip:id,trip_number','horse:id,registration_number','driver','driver.employee:id,name,surname','branch:id,name')->where('logistics_authorization', 'rejected')->where('type','Trip')->orderBy('gate_pass_number','desc')->take(100)->paginate(10);
        }elseif($this->department == "workshop"){
            return GatePass::with('trip:id,trip_number','horse:id,registration_number','driver','driver.employee:id,name,surname','branch:id,name')->where('workshop_authorization', 'rejected')->where('type','Trip')->orderBy('gate_pass_number','desc')->take(100)->paginate(10);
        }elseif($this->department == "security"){
            return GatePass::with('trip:id,trip_number','horse:id,registration_number','driver','driver.employee:id,name,surname','branch:id,name')->where('authorization', 'rejected')->where('type','Trip')->orderBy('gate_pass_number','desc')->take(10)->paginate(10);
        }

    }

      public function authorizeSelectedRows(){
       
        if (isset($this->department)) {
            
        $selected_gate_passes = GatePass::WhereIn('id',$this->selectedRows)->get();

        if (isset($selected_gate_passes)) {
             foreach($selected_gate_passes as $gate_pass){

                    if ($this->department == "logistics") {
                
                         $gate_pass->logistics_authorized_by_id = Auth::user()->id;
                         $gate_pass->logistics_authorization = $this->authorize;
                         $gate_pass->logistics_authorization_reason = $this->comments;
                         $gate_pass->update();
        
                     
                    }elseif ($this->department == "workshop") {
                        
                         $gate_pass->workshop_authorized_by_id = Auth::user()->id;
                         $gate_pass->workshop_authorization = $this->authorize;
                         $gate_pass->workshop_authorization_reason = $this->comments;                         
                         $gate_pass->update();
                
                        
                    }elseif ($this->department == "security") {
                       
                         $gate_pass->authorized_by_id = Auth::user()->id;
                         $gate_pass->authorization = $this->authorize;
                         $gate_pass->authorization_reason = $this->comments;
                         $gate_pass->update();
          
                    }
    
             }

             $this->reset(['selectedRows','selectPageRows']);

             if ($this->authorize == "approved") {

                $this->dispatchBrowserEvent('hide-authorizationModal');
                $this->dispatchBrowserEvent('alert',[
                    'type'=>'success',
                    'message'=>"Gate Pass Approved Successfully"
                ]);
                return redirect()->route('gate_passes.approved',['department' => $this->department]);
                
            }else {
                $this->dispatchBrowserEvent('hide-authorizationModal');
                $this->dispatchBrowserEvent('alert',[
                    'type'=>'success',
                    'message'=>"Gate Pass Rejected Successfully"
                ]);
                return redirect()->route('gate_passes.rejected',['department'=> $this->department]);
            }
        }
    }

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
  
                 
              }elseif ($this->department == "workshop") {
                   $gate_pass = GatePass::find($this->gate_pass_id);
                   $gate_pass->workshop_authorized_by_id = Auth::user()->id;
                   $gate_pass->workshop_authorization = $this->authorize;
                   $gate_pass->workshop_authorization_reason = $this->comments;
                   $gate_pass->update();
  
                  
              }elseif ($this->department == "security") {
                   $gate_pass = GatePass::find($this->gate_pass_id);
                   $gate_pass->authorized_by_id = Auth::user()->id;
                   $gate_pass->authorization = $this->authorize;
                   $gate_pass->authorization_reason = $this->comments;
                   $gate_pass->update();
  
                  
              }

              if ($this->authorize == "approved") {
                $this->dispatchBrowserEvent('hide-authorizationModal');
                $this->dispatchBrowserEvent('alert',[
                    'type'=>'success',
                    'message'=>"Gate Pass Approved Successfully"
                ]);
                return redirect()->route('gate_passes.approved',['department'=> $this->department]);
            }else {
                $this->dispatchBrowserEvent('hide-authorizationModal');
                $this->dispatchBrowserEvent('alert',[
                    'type'=>'success',
                    'message'=>"Gate Pass Rejected Successfully"
                ]);
                return redirect()->route('gate_passes.rejected',['department'=> $this->department]);
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
        $this->department  = $this->department;
       
        return view('livewire.gate-passes.rejected',[
            'trip_gate_passes' => $this->trip_gate_passes,
            'department' => $this->department
        ]);
    }
}
