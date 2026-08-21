<?php

namespace App\Http\Livewire\Trips;

use App\Models\Trip;
use Livewire\Component;

class TripUpdates extends Component
{
    public Trip $trip;

    protected $listeners = ['tripStatusUpdated' => '$refresh'];

    public function mount(Trip $trip): void
    {
        $this->trip = $trip;
    }

    public function render()
    {
        $trip_status_updates = $this->trip->trip_statuses()
            ->with('user')
            ->latest()
            ->latest('id')
            ->get();

        return view('livewire.trips.trip-updates', [
            'trip_status_updates' => $trip_status_updates,
        ]);
    }
}
