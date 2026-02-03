<?php

namespace App\Http\Livewire\Recoveries;

use Livewire\Component;
use App\Models\Recovery;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class Approved extends Component
{
    protected $recoveries;
    public $authorize;
    public $comments;
    public $recovery_id;


    public function mount(){
       
      }

      public function authorize($id){
        $recovery = Recovery::find($id);
        $this->recovery_id = $recovery->id;
        $this->dispatchBrowserEvent('show-authorizationModal');
      }

      public function update(){
        
        $recovery = Recovery::find($this->recovery_id);
        $recovery->authorization = $this->authorize;
        $recovery->reason = $this->comments;
        $recovery->update();
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

        return view('livewire.recoveries.approved',[
            'recoveries' => $this->recoveries = Recovery::where('authorization', 'approved')->latest()->paginate(10)
        ]);
    }
}
