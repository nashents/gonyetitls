<?php

namespace App\Http\Livewire\Invoices;


use App\Models\User;
use App\Models\Invoice;
use App\Models\Payment;
use Livewire\Component;
use Livewire\WithPagination;
use App\Models\TransportOrder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class Rejected extends Component
{

    use WithPagination;
    // public $invoices;

    protected $paginationTheme = 'bootstrap';
    public $selectedRows = [];
    public $selectPageRows = false;


    // public $invoices;
    public $invoice_id;
    public $trip_id;
    public $authorize;
    public $comments;
    public $invoice;

    public function mount(){
       

    }

    public function showBulkyAuthorize(){
        $this->dispatchBrowserEvent('show-bulkyAuthorizationModal');
      }

    public function updatedSelectPageRows($value){

        if ($value) {
            $this->selectedRows = $this->invoices->pluck('id')->map(function ($id){
                return (string) $id;
            });
        }else {
            $this->reset(['selectedRows','selectPageRows']);
        }
     
      }

      public function authorizeSelectedRows(){

         $this->validate([
            'authorize' => 'required',
        ]);

          DB::transaction(function () {

           $selected_invoices = Invoice::WhereIn('id',$this->selectedRows)->get();
           
           if (isset($selected_invoices)) {
                foreach($selected_invoices as $invoice){
                    
                    $invoice->authorized_by_id = Auth::user()->id;
                    $invoice->authorization = $this->authorize;
                    $invoice->comments = $this->comments;
                    $invoice->update();

                    if ($this->authorize == "approved") {
                            if (isset($invoice->customer_id, $invoice->currency_id)) {
                                if ($invoice->accrual_balance === null) {

                                    $customerId = $invoice->customer_id;
                                    $currencyId = $invoice->currency_id;

                                    // Payments subquery
                                    $payments = DB::table('payments')
                                        ->select([
                                            'customer_id',
                                            'currency_id',
                                            DB::raw('CAST(accrual_balance AS DECIMAL(20,2)) AS accrual_balance'),
                                            DB::raw('DATE(`date`) AS txn_date'),
                                            'created_at',
                                            DB::raw("'payment' AS source"),
                                            DB::raw('0 AS source_priority'),
                                            'id',
                                        ])
                                        ->whereNull('deleted_at') // exclude soft-deleted payments
                                        ->where('customer_id', $customerId)
                                        ->where('currency_id', $currencyId)
                                        ->whereNotNull('accrual_balance');

                                    // Invoices subquery (exclude the current invoice)
                                    $invoices = DB::table('invoices')
                                        ->select([
                                            'customer_id',
                                            'currency_id',
                                            DB::raw('CAST(accrual_balance AS DECIMAL(20,2)) AS accrual_balance'),
                                            DB::raw('DATE(`date`) AS txn_date'),
                                            'created_at',
                                            DB::raw("'invoice' AS source"),
                                            DB::raw('1 AS source_priority'),
                                            'id',
                                        ])
                                        ->where('authorization', 'approved')
                                        ->where('customer_id', $customerId)
                                        ->where('currency_id', $currencyId)
                                        ->whereNotNull('accrual_balance')
                                        ->whereNull('deleted_at') // exclude soft-deleted payments
                                        ->when(isset($invoice->id), function ($q) use ($invoice) {
                                            $q->where('id', '<>', $invoice->id);
                                        });

                                    // Union and pick the most recent by our deterministic ordering
                                    $last = DB::query()
                                        ->fromSub($payments->unionAll($invoices), 't')
                                        // prefer real transaction date; if it's null, fall back to created_at
                                        ->orderByRaw('COALESCE(t.txn_date, DATE(t.created_at)) DESC')
                                        ->orderByDesc('t.created_at')
                                        ->orderBy('t.source_priority')   // payments (0) before invoices (1) on ties
                                        ->orderByDesc('t.id')
                                        ->first();

                                   

                                    $previous_balance = ($last && is_numeric($last->accrual_balance))
                                        ? (float) $last->accrual_balance
                                        : 0.0;

                                    $invoice->accrual_balance = $previous_balance + (float) $invoice->total;
                                    $invoice->save();
                                }
                            }
                    }

                }
                if ($this->authorize == "approved") {

                $this->dispatchBrowserEvent('hide-bulkyAuthorizationModal');
                $this->dispatchBrowserEvent('alert',[
                    'type'=>'success',
                    'message'=>"Bulk Invoice(s) Approved Successfully"
                ]);
                return redirect()->route('invoices.approved');
            }else {
                $this->dispatchBrowserEvent('hide-bulkyAuthorizationModal');
                $this->dispatchBrowserEvent('alert',[
                    'type'=>'success',
                    'message'=>"Bulk Invoice(s) Rejected Successfully"
                ]);
                return redirect()->route('invoices.rejected');
            }

        

            $this->reset(['selectedRows','selectPageRows']);

            
            
           }

        });

      }

      public function getInvoicesProperty(){
        return Invoice::query()->where('authorization', 'rejected')->latest()->paginate(10);
      }

    public function authorize($id){
        $invoice = Invoice::find($id);
        $this->invoice_id = $invoice->id;
        $this->invoice = $invoice;
        $this->dispatchBrowserEvent('show-invoiceAuthorizationModal');
      }

    public function update(){

         $this->validate([
            'authorize' => 'required',
        ]);

        DB::transaction(function () {
      try{
            $invoice = Invoice::find($this->invoice_id);
            $invoice->authorized_by_id = Auth::user()->id;
            $invoice->authorization = $this->authorize;
            $invoice->comments = $this->comments;
            $invoice->update();

        if ($this->authorize == "approved") {

                  if (isset($invoice->customer_id, $invoice->currency_id)) {
                if ($invoice->accrual_balance === null) {

                    $customerId = $invoice->customer_id;
                    $currencyId = $invoice->currency_id;

                    // Payments subquery
                    $payments = DB::table('payments')
                        ->select([
                            'customer_id',
                            'currency_id',
                            DB::raw('CAST(accrual_balance AS DECIMAL(20,2)) AS accrual_balance'),
                            DB::raw('DATE(`date`) AS txn_date'),
                            'created_at',
                            DB::raw("'payment' AS source"),
                            DB::raw('0 AS source_priority'),
                            'id',
                        ])
                         ->whereNull('deleted_at') // exclude soft-deleted payments
                        ->where('customer_id', $customerId)
                        ->where('currency_id', $currencyId)
                        ->whereNotNull('accrual_balance');

                    // Invoices subquery (exclude the current invoice)
                    $invoices = DB::table('invoices')
                        ->select([
                            'customer_id',
                            'currency_id',
                            DB::raw('CAST(accrual_balance AS DECIMAL(20,2)) AS accrual_balance'),
                            DB::raw('DATE(`date`) AS txn_date'),
                            'created_at',
                            DB::raw("'invoice' AS source"),
                            DB::raw('1 AS source_priority'),
                            'id',
                        ])
                        ->where('authorization', 'approved')
                        ->where('customer_id', $customerId)
                        ->where('currency_id', $currencyId)
                        ->whereNotNull('accrual_balance')
                        ->whereNull('deleted_at') // exclude soft-deleted payments
                        ->when(isset($invoice->id), function ($q) use ($invoice) {
                            $q->where('id', '<>', $invoice->id);
                        });

                    // Union and pick the most recent by our deterministic ordering
                    $last = DB::query()
                        ->fromSub($payments->unionAll($invoices), 't')
                        // prefer real transaction date; if it's null, fall back to created_at
                        ->orderByRaw('COALESCE(t.txn_date, DATE(t.created_at)) DESC')
                        ->orderByDesc('t.created_at')
                        ->orderBy('t.source_priority')   // payments (0) before invoices (1) on ties
                        ->orderByDesc('t.id')
                        ->first();

                  

                    $previous_balance = ($last && is_numeric($last->accrual_balance))
                        ? (float) $last->accrual_balance
                        : 0.0;

                    $invoice->accrual_balance = $previous_balance + (float) $invoice->total;
                    $invoice->save();
                }
            }

            $this->dispatchBrowserEvent('hide-invoiceAuthorizationModal');
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Invoice Approved Successfully"
            ]);
            return redirect()->route('invoices.approved');
        }else {
            $this->dispatchBrowserEvent('hide-invoiceAuthorizationModal');
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Invoice Rejected Successfully"
            ]);
            return redirect()->route('invoices.rejected');
        }
}
catch(\Exception $e){
    $this->dispatchBrowserEvent('hide-invoiceEditModal');
    $this->dispatchBrowserEvent('alert',[
        'type'=>'error',
        'message'=>"Something went wrong while trying to authorize an invoice!!"
    ]);
    }

    });

      }
    public function render()
    {
        return view('livewire.invoices.rejected',[
            'invoices' => $this->invoices
        ]);
    }
}
