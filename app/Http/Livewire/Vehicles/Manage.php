<?php

namespace App\Http\Livewire\Vehicles;

use App\Models\Vehicle;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class Manage extends Component
{


    public $vehicles;


    public function mount(){
        $departments = Auth::user()->employee->departments;
        foreach($departments as $department){
            $department_names[] = $department->name;
        }
        $roles = Auth::user()->roles;
        foreach($roles as $role){
            $role_names[] = $role->name;
        }
        $ranks = Auth::user()->employee->ranks;
        foreach($ranks as $rank){
            $rank_names[] = $rank->name;
        }
        if (in_array('Admin', $role_names) || in_array('Super Admin', $role_names)) {
            $this->vehicles = Vehicle::latest()->get();
        } else {
            $this->vehicles = Vehicle::where('user_id',Auth::user()->id)->latest()->get();
        }
      }


    public function deactivate($id){
        $vehicle = Vehicle::find($id);
        $vehicle->status = 0 ;
        $vehicle->update();
        Session::flash('success','Vehicle successfully deactivated');
        return redirect(route('vehicles.manage'));
    }

    public function activate($id){
        $vehicle = Vehicle::find($id);
        $vehicle->status = 1 ;
        $vehicle->update();
        Session::flash('success','Vehicle successfully deactivated');
        return redirect(route('vehicles.manage'));
    }

    public function render()
    {
        return view('livewire.vehicles.manage');
    }
}
