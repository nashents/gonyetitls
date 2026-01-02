<?php

namespace App\Http\Livewire\Invoices;

use App\Models\Invoice;
use Livewire\Component;
use Livewire\WithPagination;

class Show extends Component
{
     use WithPagination;
    protected $paginationTheme = 'bootstrap';
    public $invoice;
    public $invoices;
    public $invoice_id;
    protected $payments;

    public function mount($id){
        $this->invoice = Invoice::withTrashed()->find($id);
    }
    public function render()
    {
        return view('livewire.invoices.show',[
            'payments' => $this->invoice->payments()->paginate(10),
        ]);
    }
}
