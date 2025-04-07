<?php

namespace App\Http\Livewire\Purchases;

use Livewire\Component;
use App\Models\Purchase;
use Barryvdh\DomPDF\PDF;

class Preview extends Component
{
    public $purchase;
    public $purchase_products;
    public $company;

    public function mount($purchase, $purchase_products, $company){
        $this->purchase = $purchase;
        $this->purchase_products = $purchase_products;
        $this->company = $company;
    }

    public function render()
    {
        return view('livewire.purchases.preview');
    }
}
