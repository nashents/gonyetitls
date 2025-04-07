<?php

namespace App\Http\Livewire\Trips;

use App\Models\Trip;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class Deleted extends Component
{

    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    private $trips;
    public $trip_id;


    public function showRestore($id){
        $this->trip_id = $id;
        $this->dispatchBrowserEvent('show-tripRestoreModal');
    }
    public function update(){

        $trip = Trip::withTrashed()->find($this->trip_id)->restore();
        
        $transportation_order = $trip->transport_order->withTrashed();
        $transportation_order->restore();
        $fuels = $trip->fuels->withTrashed();
        if (isset($fuels)) {
            foreach($fuels as $fuel){
                $fuel->restore();
            }
        }
        $delivery_note = $trip->delivery_note->withTrashed();
        $delivery_note->restore();
        
        $cash_flows = $trip->cash_flows->withTrashed();
        if (isset($cash_flows)) {
            foreach($cash_flows as $cash_flow){
                $cash_flow->restore();
            }
        }
        $expenses = $trip->trip_expenses->withTrashed();
        if (isset($expenses)) {
            foreach($expenses as $expense){
                $expense->restore();
            }
        }
        $bills = $trip->bills->withTrashed();
        if (isset($bills)) {
            foreach($bills as $bill){
                $bill->restore();
            }
        }

        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Trip Restored Successfully!!"
        ]);
        $this->dispatchBrowserEvent('hide-tripRestoreModal');
       
    }
    public function render()
    {
       
        return view('livewire.trips.deleted',[
            'trips' => Trip::onlyTrashed()->orderBy('deleted_at','desc')->paginate(10),
        ]);
    }
}
