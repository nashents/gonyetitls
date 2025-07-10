<?php

namespace App\Http\Livewire\TransportOrders;

use App\Models\Trip;
use App\Models\User;
use Livewire\Component;
use App\Models\Destination;
use Illuminate\Support\Facades\Auth;

class Preview extends Component
{
    public $company;
    public $trip;
    public $origin;
    public $destination;
    public $pattern;
    public $authorizer;
    

    public function mount($id){
        $this->company = Auth::user()->employee->company;
        $this->trip = Trip::with([
        'customer:id,name',
        'driver.employee',
        'horse' => function ($q) {
            $q->select('id', 'registration_number', 'fleet_number', 'horse_make_id', 'horse_model_id')
            ->with([
                'horse_make:id,name',
                'horse_model:id,name',
            ]);
        },
        'transporter:id,name',
        ])->find($id);
        $this->pattern = '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/';
        $this->origin = Destination::find($this->trip->from);
        $this->destination = Destination::find($this->trip->to);
        $this->authorizer = User::find($this->trip->authorized_by_id);
        
     
    }

    public function render()
    {
        return view('livewire.transport-orders.preview');
    }
}
