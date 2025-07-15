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
    public function update()
    {
        $trip = Trip::withTrashed()->find($this->trip_id);

        if (!$trip) {
            $this->dispatchBrowserEvent('alert', [
                'type' => 'error',
                'message' => "Trip not found!"
            ]);
            return;
        }

        $trip->restore();

        // Restore related fuels
        $fuels = $trip->fuels()->withTrashed()->get();
        foreach ($fuels as $fuel) {
            $fuel->restore();
        }

        // Restore delivery note
        $delivery_note = $trip->delivery_note()->withTrashed()->first();
        if ($delivery_note) {
            $delivery_note->restore();
        }

        // Restore cash flows
        $cash_flows = $trip->cash_flows()->withTrashed()->get();
        foreach ($cash_flows as $cash_flow) {
            $cash_flow->restore();
        }

        // Restore expenses
        $expenses = $trip->trip_expenses()->withTrashed()->get();
        foreach ($expenses as $expense) {
            $expense->restore();
        }

        // Restore bills
        $bills = $trip->bills()->withTrashed()->get();
        foreach ($bills as $bill) {
            $bill->restore();
        }

        $this->dispatchBrowserEvent('alert', [
            'type' => 'success',
            'message' => "Trip Restored Successfully!!"
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
