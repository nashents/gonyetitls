<?php

namespace App\Http\Livewire\Receipts;

use Livewire\Component;

class Preview extends Component
{
    public $receipt;
    public $invoice;
    public $invoice_items;
    public $sale;
    public $sale_items;
    public $company;
    public $receipts;
    public $receipt_id;

    public function mount($receipt, $company){
            $this->receipt = $receipt;
            $this->company = $company;
            $this->invoice = $this->receipt->invoice;
            $this->sale = $this->receipt->sale;
            if (isset($this->invoice)) {
                $this->invoice_items = $this->invoice->invoice_items;
            }
            if (isset($this->sale)) {
                $this->sale_items = $this->sale->sale_items;
            }
        
    }
    public function render()
    {
        return view('livewire.receipts.preview');
    }
}
