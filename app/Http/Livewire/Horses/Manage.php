<?php

namespace App\Http\Livewire\Horses;

use App\Models\Horse;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class Manage extends Component
{


    public $horses;


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
            $this->horses = Horse::with('transporter:id,name','horse_make:id,name','horse_model:id,name')->orderBy('registration_number','asc')->get();
        } else {
            $this->horses = Horse::with('transporter:id,name','horse_make:id,name','horse_model:id,name')->where('user_id',Auth::user()->id)->orderBy('registration_number','asc')->get();
        }
      }


    public function deactivate($id){
        $horse = Horse::find($id);
        $horse->status = 0 ;
        $horse->update();
        Session::flash('success','Horse successfully deactivated');
        return redirect(route('horses.manage'));
    }

    public function activate($id){
        $horse = Horse::find($id);
        $horse->status = 1 ;
        $horse->update();
        Session::flash('success','Horse successfully deactivated');
        return redirect(route('horses.manage'));
    }

    public function render()
    {
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
            $this->horses = Horse::with('transporter:id,name','horse_make:id,name','horse_model:id,name')->orderBy('registration_number','asc')->get();
        } else {
            $this->horses = Horse::with('transporter:id,name','horse_make:id,name','horse_model:id,name')->where('user_id',Auth::user()->id)->orderBy('registration_number','asc')->get();
        }

        return view('livewire.horses.manage',[
            'horses' => $this->horses
        ]);
    }
}
