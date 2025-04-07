<?php

namespace App\Http\Livewire\Brokers;

use App\Models\Broker;
use Livewire\Component;

class Reports extends Component
{
    public $search = NULL;
    public $to;
    public $from;
    public $brokers;

    public function search(){

        $this->search = TRUE;
        $this->brokers = Broker::whereBetween('created_at',[$this->from, $this->to] )
        ->latest()->get();

    }
    public function render()
    {
        return view('livewire.brokers.reports');
    }
}
