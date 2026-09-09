<?php

namespace App\Http\Livewire\Invoices;

use App\Models\User;
use App\Models\Invoice;
use App\Services\Accounting\InvoiceRestorationService;
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

        $invoice = Invoice::withTrashed()->findOrFail($this->invoice_id);

        try {
            app(InvoiceRestorationService::class)->restore($invoice);
        } catch (\Throwable $e) {
            $this->dispatchBrowserEvent('hide-invoiceRestoreModal');
            $this->dispatchBrowserEvent('alert',[
                'type'=>'danger',
                'message'=>$e->getMessage(),
            ]);
            return;
        }

        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Invoice Restored Successfully!! Its account balances and journal entries have been reapplied."
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
