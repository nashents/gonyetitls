<?php

namespace App\Http\Livewire\Invoices;

use App\Models\User;
use App\Models\Invoice;
use Livewire\Component;
use Livewire\WithPagination;
use App\Models\TransportOrder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class Deleted extends Component
{

    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $search;
    protected $queryString = ['search'];

    private $invoices;
    public $invoice_id;
    public $trip_id;
    public $authorize;
    public $comments;
    public $invoice;

    
    public function restore($id){
        $this->invoice_id = $id;
        $this->dispatchBrowserEvent('show-invoiceRestoreModal');
    }
    public function update(){

        $invoice =  Invoice::withTrashed()->find($this->invoice_id);
        $invoice->bills()->withTrashed()->restore();
        $invoice->bills->each(function ($bill) {
            $bill->bill_expenses()->withTrashed()->restore();
        });
        $invoice->payments()->withTrashed()->restore();

        $invoice->payments->each(function ($payment) {
            $payment->cash_flow()->withTrashed()->restore();
        });
        $invoice->payments->each(function ($payment) {
            $payment->receipt()->withTrashed()->restore();
        });
        $invoice->payments->each(function ($payment) {
            $payment->denominations()->withTrashed()->restore();
        });

        $invoice->invoice_items()->withTrashed()->restore();
       

        $invoice =  Invoice::withTrashed()->find($this->invoice_id)->restore();

        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Invoice Restored Successfully!!"
        ]);
        $this->dispatchBrowserEvent('hide-invoiceRestoreModal');
        return redirect()->route('invoices.index');
       
    }


    public function render()
    {
       
        return view('livewire.invoices.deleted',[
            'invoices' => Invoice::onlyTrashed()->orderBy('deleted_at','desc')->paginate(10),
        ]);
    }
}
