<?php

namespace App\Http\Livewire\Transporters;

use App\Models\Transporter;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class Deleted extends Component
{
    public $transporters;
    public $trip_id;

    public function mount(){
        $period = Auth::user()->employee->company->period;
        if (isset( $period)) {
            if ($period != "all") {
                $this->transporters = Transporter::onlyTrashed()->whereYear('created_at',$period)->latest()->get();
            }else {
                $this->transporters = Transporter::onlyTrashed()->latest()->get();
            }
        }
    }

    public function restore($id){
        $this->trip_id = $id;
        $this->dispatchBrowserEvent('show-tripRestoreModal');
    }
    public function update(){
        Transporter::withTrashed()->find($this->trip_id)->restore();
        Session::flash('success','Trip Restored Successfully');
        $this->dispatchBrowserEvent('hide-tripRestoreModal');
        return redirect()->route('transporters.deleted');
    }
    public function render()
    {
        return view('livewire.transporters.deleted');
    }
}
