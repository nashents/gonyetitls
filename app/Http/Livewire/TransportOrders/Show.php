<?php

namespace App\Http\Livewire\TransportOrders;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Show extends Component
{
    public $transport_order;
    public $company;
    public $department_names;
    public $role_names;
    public $rank_names;
    public $employee;

    public function mount($transport_order){
        $this->transport_order = $transport_order;
        $this->employee = Auth::user()->employee;
        $this->company = $this->employee->company;
         $departments = $this->employee->departments;
         foreach($departments as $department){
             $this->department_names[] = $department->name;
         }
         $roles = Auth::user()->roles;
         foreach($roles as $role){
             $this->role_names[] = $role->name;
         }
         $ranks = $this->employee->ranks;
         foreach($ranks as $rank){
             $this->rank_names[] = $rank->name;
         }
    }
    public function render()
    {
        return view('livewire.transport-orders.show');
    }
}
