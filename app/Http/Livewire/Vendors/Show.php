<?php

namespace App\Http\Livewire\Vendors;

use App\Models\Vendor;
use Livewire\Component;

class Show extends Component
{

    public $vendor;
    public $bills;


    public function mount($id){
        $this->vendor = Vendor::find($id);
        $this->bills = $this->vendor->bills;
    }
    public function render()
    {
        return view('livewire.vendors.show');
    }
}
