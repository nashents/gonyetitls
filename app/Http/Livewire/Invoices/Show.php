<?php

namespace App\Http\Livewire\Invoices;

use App\Models\Invoice;
use Livewire\Component;

class Show extends Component
{
    public $invoice;
    public $invoices;
    public $invoice_id;

    public function mount($id){
        $this->invoice = Invoice::withTrashed()->find($id);
    }
    public function render()
    {
        return view('livewire.invoices.show');
    }
}
