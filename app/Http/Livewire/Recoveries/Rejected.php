<?php

namespace App\Http\Livewire\Recoveries;

use Livewire\Component;
use App\Models\Recovery;
use Illuminate\Support\Facades\Auth;

class Rejected extends Component
{
    public $recoveries;
    public $recovery_id;
    public $trip_id;
    public $authorize;
    public $comments;
    public $recovery;

    public function mount(){
        $period = Auth::user()->employee->company->period;
        if (isset( $period)) {
            if ($period != "all") {
                $this->recoveries = Recovery::where('authorization', 'rejected')->whereYear('created_at',$period)->latest()->get();
            }else {
                $this->recoveries = Recovery::where('authorization', 'rejected')->latest()->get();
            }
        }

    }
    public function authorize($id){
        $recovery = Recovery::find($id);
        $this->recovery_id = $recovery->id;
        $this->recovery = $recovery;
        $this->dispatchBrowserEvent('show-authorizationModal');
      }

      public function update(){
      try{
            $recovery = Recovery::find($this->recovery_id);
            $recovery->authorization = $this->authorize;
            $recovery->reason = $this->comments;
            $recovery->update();

        if ($this->authorize == "approved") {
            $this->dispatchBrowserEvent('hide-authorizationModal');
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"recovery Approved Successfully"
            ]);
            return redirect()->route('recoveries.approved');
        }else {
            $this->dispatchBrowserEvent('hide-authorizationModal');
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"recovery Rejected Successfully"
            ]);
            return redirect()->route('recoveries.rejected');
        }
    }
    catch(\Exception $e){
        $this->dispatchBrowserEvent('hide-authorizationModal');
        $this->dispatchBrowserEvent('alert',[
            'type'=>'error',
            'message'=>"Something went wrong while trying to authorize recovery!!"
        ]);
        }

      }
    public function render()
    {
        return view('livewire.recoveries.rejected');
    }
}
