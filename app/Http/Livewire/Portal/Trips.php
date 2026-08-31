<?php

namespace App\Http\Livewire\Portal;

use App\Models\Trip;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class Trips extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public function render()
    {
        $trips = Trip::where('customer_id', Auth::guard('customer')->id())
            ->with(['fromDestination', 'toDestination'])
            ->orderByDesc('start_date')
            ->paginate(10);

        return view('livewire.portal.trips', ['trips' => $trips]);
    }
}
