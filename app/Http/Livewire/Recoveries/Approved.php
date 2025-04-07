<?php

namespace App\Http\Livewire\Recoveries;

use Livewire\Component;
use App\Models\Recovery;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class Approved extends Component
{
    public $recoveries;
    public $authorize;
    public $comments;
    public $recovery_id;


    public function mount(){
        $period = Auth::user()->employee->company->period;
        if (isset( $period)) {
            if ($period != "all") {
                $this->recoveries = Recovery::where('authorization', 'approved')->whereYear('created_at',$period)->latest()->get();
            }else {
                $this->recoveries = Recovery::where('authorization', 'approved')->latest()->get();
            }
        }
      }

      public function authorize($id){
        $recovery = Recovery::find($id);
        $this->recovery_id = $recovery->id;
        $this->dispatchBrowserEvent('show-authorizationModal');
      }

      public function update(){
        
        $recovery = Recovery::find($this->recovery_id);
        $trip->authorization = $this->authorize;
        $trip->reason = $this->comments;
        $trip->update();
        if ($this->authorize == 'approved') {
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Recovery Approved Already"
            ]);
            $this->dispatchBrowserEvent('hide-authorizationModal');
        }
      }
    public function render()
    {
        return view('livewire.recoveries.approved');
    }
}
