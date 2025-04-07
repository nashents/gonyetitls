<?php

namespace App\Http\Livewire\Drivers;

use App\Models\Driver;
use Livewire\Component;
use Illuminate\Support\Facades\Session;

class Deleted extends Component
{
    public $drivers;
    public $driver_id;

    public function mount(){
        $this->drivers = Driver::onlyTrashed()->latest()->get();
    }

    public function restore($id){
        $this->driver_id = $id;
        $this->dispatchBrowserEvent('show-driverRestoreModal');
    }
    public function update(){
        $driver =  Driver::withTrashed()->find($this->driver_id);
        $employee_id = $driver->employee_id;
        $user_id = $driver->user_id;
        Driver::withTrashed()->find($this->driver_id)->restore();
        Employee::withTrashed()->find($employee_id)->restore();
        User::withTrashed()->find( $user_id)->restore();
        Session::flash('success','Driver Restored Successfully!!');
        $this->dispatchBrowserEvent('hide-driverRestoreModal');
        return redirect()->route('drivers.deleted');
    }
    public function render()
    {
        return view('livewire.drivers.deleted');
    }
}
