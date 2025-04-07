<?php

namespace App\Http\Livewire\Assets;

use App\Models\Asset;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class Manage extends Component
{

    public $assets;
    public $asset_id;

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
            $this->assets = Asset::latest()->get();
        } else {
            $this->assets = Asset::where('user_id',Auth::user()->id)->latest()->get();
        }
    }

    public function render()
    {
        return view('livewire.assets.manage',[
            'assets' => $this->assets
        ]);
    }
}
