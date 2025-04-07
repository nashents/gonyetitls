<?php

namespace App\Http\Livewire\Drivers;

use App\Models\Driver;
use Livewire\Component;
use Illuminate\Support\Facades\Session;

class Archived extends Component
{

    public $drivers;
    public $driver_id;
  
    public function mount(){
        $this->drivers = Driver::where('archive','1')
                                    ->orderBy('driver_number', 'desc')->get();
      }

      public function restore($id){
        $this->driver_id = $id;
        $this->dispatchBrowserEvent('show-driverRestoreModal');
    }
    public function update(){
        $driver =  Driver::withTrashed()->find($this->driver_id);
        $driver->archive = 0;
        $driver->update();
        Session::flash('success','Driver Restored Successfully!!');
        $this->dispatchBrowserEvent('hide-driverRestoreModal');
        return redirect()->route('drivers.index');
    }


    public function render()
    {
        return view('livewire.drivers.archived');
    }
}
