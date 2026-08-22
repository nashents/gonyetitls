<?php

namespace App\Http\Livewire\GatePasses;

use Livewire\Component;
use App\Http\Livewire\Concerns\HasStayOnPageAuthorization;
use App\Models\GatePass;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

class Pending extends Component
{

    use WithPagination, HasStayOnPageAuthorization;

    protected $paginationTheme = 'bootstrap';
    public $search;
    public bool $notificationsOnly = false;
    protected $queryString = ['search', 'notificationsOnly' => ['as' => 'notifications', 'except' => false]];
    public $from;
    public $to;


  
    public $gate_pass_id;
    public $authorize;
    public $comments;
    public $gate_pass;
    public $gate_pass_filter;
    public $department;

    public $selectedRows = [];
    public $selectPageRows = false;

    public function mount($department){
        $this->department = $department;
        $this->gate_pass_filter = 'created_at';
        $this->notificationsOnly = request()->boolean('notifications', false);

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

     public function getTripGatePassesProperty()
    {
        $query = GatePass::query()
            ->with([
                'trip:id,trip_number',
                'horse:id,registration_number',
                'driver',
                'driver.employee:id,name,surname',
                'branch:id,name',
            ])
            ->where('type', 'Trip');

        // Department authorization logic
        if ($this->department == "logistics") {

            $query->where('logistics_authorization', 'pending');

        } elseif ($this->department == "workshop") {

            $query->where('workshop_authorization', 'pending');

        } elseif ($this->department == "security") {

            $query->where('authorization', 'pending');
        }

        // Date filtering
        if ($this->notificationsOnly) {
            $query->whereYear($this->gate_pass_filter, now()->year);
        }else{
            if (!empty($this->from) && !empty($this->to)) {

                $query->whereBetween($this->gate_pass_filter, [
                    $this->from,
                    $this->to
                ]);

            } else {

                $query->whereMonth($this->gate_pass_filter, now()->month)
                    ->whereYear($this->gate_pass_filter, now()->year);
            }
        }

        return $query
            ->latest()
            ->take(100)
            ->paginate(10);
    }

      public function authorizeSelectedRows(){
       
        if (isset($this->department)) {
            
        $selected_gate_passes = GatePass::WhereIn('id',$this->selectedRows)->get();

        if (isset($selected_gate_passes)) {
             foreach($selected_gate_passes as $gate_pass){

                    if ($this->department == "logistics") {
                         $gate_pass->logistics_authorized_by_id = Auth::user()->employee->id;
                         $gate_pass->logistics_authorization = $this->authorize;
                         $gate_pass->logistics_authorization_reason = $this->comments;
                         $gate_pass->update();
                    }elseif ($this->department == "workshop") {
                         $gate_pass->workshop_authorized_by_id = Auth::user()->employee->id;
                         $gate_pass->workshop_authorization = $this->authorize;
                         $gate_pass->workshop_authorization_reason = $this->comments;                         
                         $gate_pass->update(); 
                    }elseif ($this->department == "security") {
                         $gate_pass->authorized_by_id = Auth::user()->employee->id;
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
                return $this->redirectOrStay('gate_passes.approved',['department' => $this->department]);

            }else {
                $this->dispatchBrowserEvent('hide-authorizationModal');
                $this->dispatchBrowserEvent('alert',[
                    'type'=>'success',
                    'message'=>"Gate Pass Rejected Successfully"
                ]);
                return $this->redirectOrStay('gate_passes.rejected',['department'=> $this->department]);
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
        // try{
          if (isset($this->department)) {

              if ($this->department == "logistics") {
                   $gate_pass = GatePass::find($this->gate_pass_id);
                   $gate_pass->logistics_authorized_by_id = Auth::user()->employee->id;
                   $gate_pass->logistics_authorization = $this->authorize;
                   $gate_pass->logistics_authorization_reason = $this->comments;
                   $gate_pass->update();
  
                 
              }elseif ($this->department == "workshop") {
                   $gate_pass = GatePass::find($this->gate_pass_id);
                   $gate_pass->workshop_authorized_by_id = Auth::user()->employee->id;
                   $gate_pass->workshop_authorization = $this->authorize;
                   $gate_pass->workshop_authorization_reason = $this->comments;
                   $gate_pass->update();
  
                  
              }elseif ($this->department == "security") {
                   $gate_pass = GatePass::find($this->gate_pass_id);
                   $gate_pass->authorized_by_id = Auth::user()->employee->id;
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
                return $this->redirectOrStay('gate_passes.approved',['department'=> $this->department]);
            }else {
                $this->dispatchBrowserEvent('hide-authorizationModal');
                $this->dispatchBrowserEvent('alert',[
                    'type'=>'success',
                    'message'=>"Gate Pass Rejected Successfully"
                ]);
                return $this->redirectOrStay('gate_passes.rejected',['department'=> $this->department]);
            }

           }
  
      
    //   }
    //   catch(\Exception $e){
    //       $this->dispatchBrowserEvent('hide-authorizationModal');
    //       $this->dispatchBrowserEvent('alert',[
    //           'type'=>'error',
    //           'message'=>"Something went wrong while trying to authorize Gate Pass!!"
    //       ]);
    //       }
  
        }


  


    public function render()
    {
        $this->department  = $this->department;
       
        return view('livewire.gate-passes.pending',[
            'trip_gate_passes' => $this->trip_gate_passes,
            'department' => $this->department
        ]);
    }
}
