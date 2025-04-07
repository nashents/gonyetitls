<?php

namespace App\Http\Livewire\Inventories;

use Livewire\Component;
use App\Models\Inventory;
use Illuminate\Support\Facades\Auth;

class Manage extends Component
{
    public $inventories;
    public $inventory_id;

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
            $this->inventories = Inventory::latest()->get();
        } else {
            $this->inventories = Inventory::where('user_id',Auth::user()->id)->get();
        }
    }

    public function render()
    {
        return view('livewire.inventories.manage',[
            'inventories' => $this->inventories
        ]);
    }
}
