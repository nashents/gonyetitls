<?php

namespace App\Http\Livewire\Vendors;

use App\Models\Vendor;
use Livewire\Component;

class Reports extends Component
{

    public $search = NULL;
    public $to;
    public $from;
    public $vendors;

    public function search(){

        $this->search = TRUE;
        $this->vendors = Vendor::whereBetween('created_at',[$this->from, $this->to] )
        ->latest()->get();

    }
    public function render()
    {
        return view('livewire.vendors.reports');
    }
}
