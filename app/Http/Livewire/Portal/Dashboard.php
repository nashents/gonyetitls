<?php

namespace App\Http\Livewire\Portal;

use App\Models\FreightJob;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class Dashboard extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public function render()
    {
        $jobs = FreightJob::where('customer_id', Auth::guard('customer')->id())
            ->orderByDesc('opened_at')
            ->paginate(10);

        return view('livewire.portal.dashboard', ['jobs' => $jobs]);
    }
}
