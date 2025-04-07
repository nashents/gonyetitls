<?php

namespace App\Http\Livewire\Transporters;

use App\Models\Transporter;
use Livewire\Component;
use App\Models\TransportOrder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class Approved extends Component
{
    public $transporters;
    public $authorize;
    public $comments;
    public $transporter_id;


    public function mount(){
        $period = Auth::user()->employee->company->period;
        if (isset( $period)) {
            if ($period != "all") {
                $this->transporters = Transporter::where('authorization', 'approved')->whereYear('created_at',$period)->latest()->get();
            }else {
                $this->transporters = Transporter::where('authorization', 'approved')->latest()->get();
            }
        }
      }

      public function authorize($id){
        $transporter = Transporter::find($id);
        $this->transporter_id = $transporter->id;
        $this->dispatchBrowserEvent('show-authorizationModal');
      }

      public function update(){
        
        $transporter = Transporter::find($this->transporter_id);
        $transporter->authorized_by_id = Auth::user()->id;
        $transporter->authorization = $this->authorize;
        $transporter->reason = $this->comments;
        $transporter->update();
        Session::flash('success','Authorization decision effected successfully');
        $this->dispatchBrowserEvent('hide-authorizationModal');
        if ($this->authorize == 'approved') {
            return redirect()->route('transporters.approved');
        }else {
            return redirect()->route('transporters.rejected');
        }
      }
    public function render()
    {
        return view('livewire.transporters.approved');
    }
}
