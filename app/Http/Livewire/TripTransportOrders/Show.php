<?php

namespace App\Http\Livewire\TripTransportOrders;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Show extends Component
{
    public $tto;
    public $company;
    public $department_names;
    public $role_names;
    public $rank_names;
    public $employee;
    public $trip;
    public $transport_order;

    public function mount($trip_transport_order){

        $this->tto = $trip_transport_order;
        $this->trip = $this->tto?->trip;
        $this->transport_order = $this->tto?->transport_order;
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
        return view('livewire.trip-transport-orders.show');
    }
}
