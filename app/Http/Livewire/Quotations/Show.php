<?php

namespace App\Http\Livewire\Quotations;

use Livewire\Component;
use App\Models\Quotation;

class Show extends Component
{

    public $quotation;
    public $quotation_id;
    public $trips;
    public $trip_id;
    public $quotation_products;
    public $quotation_product_id;

    public function mount($id){
        $this->quotation = Quotation::find($id);
        $this->quotation_products = $this->quotation->quotation_products;
        $this->trips = $this->quotation->trips;
    }
    public function render()
    {
        return view('livewire.quotations.show');
    }
}
