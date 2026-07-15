<?php

namespace App\Http\Livewire\Deals;

use App\Models\Deal;
use Livewire\Component;

class Show extends Component
{
    public $deal;
    public $trips;

    public function mount(Deal $deal)
    {
        $this->deal = $deal;
        $this->trips = $deal->trips()->with('units_of_measure')->orderBy('created_at', 'desc')->get();
    }

    public function render()
    {
        return view('livewire.deals.show');
    }
}
