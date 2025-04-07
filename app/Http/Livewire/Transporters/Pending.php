<?php

namespace App\Http\Livewire\Transporters;

use App\Models\User;
use App\Models\Transporter;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;

class Pending extends Component
{
    public $transporters;
    public $transporter_id;
    public $trip_id;
    public $authorize;
    public $comments;
    public $transporter;

    public function mount(){
        $period = Auth::user()->employee->company->period;
        if (isset( $period)) {
            if ($period != "all") {
                $this->transporters = Transporter::where('authorization', 'pending')->whereYear('created_at',$period)->latest()->get();
            }else {
                $this->transporters = Transporter::where('authorization', 'pending')->latest()->get();
            }
        }

    }
    public function authorize($id){
        $transporter = Transporter::find($id);
        $this->transporter_id = $transporter->id;
        $this->transporter = $transporter;
        $this->dispatchBrowserEvent('show-authorizationModal');
      }

      public function update(){
      try{
            $transporter = Transporter::find($this->transporter_id);
            $transporter->authorized_by_id = Auth::user()->id;
            $transporter->authorization = $this->authorize;
            $transporter->reason = $this->comments;
            $transporter->update();

        if ($this->authorize == "approved") {
            $this->dispatchBrowserEvent('hide-authorizationModal');
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Transporter Approved Successfully"
            ]);
            return redirect()->route('transporters.approved');
        }else {
            $this->dispatchBrowserEvent('hide-authorizationModal');
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Transporter Rejected Successfully"
            ]);
            return redirect()->route('transporters.rejected');
        }
}
catch(\Exception $e){
    $this->dispatchBrowserEvent('hide-authorizationModal');
    $this->dispatchBrowserEvent('alert',[
        'type'=>'error',
        'message'=>"Something went wrong while trying to authorize Transporter!!"
    ]);
    }

      }
    public function render()
    {
        $this->transporters = Transporter::where('authorization', 'pending')->latest()->get();
        return view('livewire.transporters.pending',[
            'transporters' => $this->transporters
        ]);
    }
}
