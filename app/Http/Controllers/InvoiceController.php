<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Customer;
use App\Models\Currency;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendCustomerStatementMail;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use App\Services\CustomerLedgerService;

class InvoiceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('invoices.index');
    }
   
    public function customerStatements()
    {
        return view('customer_statements.index');
    }
    
    public function customerStatementsPreview($selectedCustomer = null, $selectedType = null, $from = null, $to = null){
        return view('customer_statements.preview')->with([
            'selectedCustomer' => $selectedCustomer,
            'selectedType' => $selectedType,
            'from' => $from,
            'to' => $to,
            ]);
    }

    /**
     * Account Activity figures derived live from the transaction ledger
     * (invoices, payments, approved credit notes) for every currency the
     * customer has been invoiced in. Never reads a stored balance column.
     */
    private function accountActivityData($selectedCustomer, $from, $to): array
    {
        $service = app(CustomerLedgerService::class);
        $currencies = Currency::all();

        $openingBalances = [];
        $closingBalances = [];
        $results = collect();

        foreach ($currencies as $currency) {
            $openingBalances[$currency->id] = $service->openingBalance((int) $selectedCustomer, $currency->id, $from);
            $closingBalances[$currency->id] = $service->closingBalance((int) $selectedCustomer, $currency->id, $to);
            $results = $results->merge($service->activity((int) $selectedCustomer, $currency->id, $from, $to));
        }

        return [
            'results' => $results,
            'openingBalances' => $openingBalances,
            'closingBalances' => $closingBalances,
        ];
    }

    public function customerStatementsPrint($selectedCustomer = null, $selectedType = null, $from = null, $to = null){
        $company = Auth::user()->employee->company;
        $customer = Customer::find($selectedCustomer);
        if ( isset($selectedCustomer) && $selectedType == "Outstanding Invoices") {

            $invoices = Invoice::where('customer_id', $selectedCustomer)
            ->where('authorization', 'approved')
            ->where('status', 'Unpaid')
            ->orWhere('customer_id', $selectedCustomer)
            ->where('authorization','approved')
            ->where('status', 'Partial')->get();

            return view('customer_statements.print')->with([
                'selectedCustomer' => $selectedCustomer,
                'selectedType' => $selectedType,
                'from' => $from,
                'to' => $to,
                'invoices' => $invoices,
                'company' => $company,
                'customer' => $customer,
                ]);
    
        }elseif ( isset($selectedCustomer) && $selectedType == "Account Activity") {
            $activity = [];
            if (isset($from) && isset($to)) {
                $activity = $this->accountActivityData($selectedCustomer, $from, $to);
            }
            return view('customer_statements.print')->with([
                'selectedCustomer' => $selectedCustomer,
                'selectedType' => $selectedType,
                'from' => $from,
                'to' => $to,
                'invoices' => $activity['results'] ?? null,
                'results' => $activity['results'] ?? null,
                'openingBalances' => $activity['openingBalances'] ?? [],
                'closingBalances' => $activity['closingBalances'] ?? [],
                'company' => $company,
                'customer' => $customer,
                ]);
        }


    }

    public function customerStatementsPDF($selectedCustomer = null, $selectedType = null, $from = null, $to = null){
        $company = Auth::user()->employee->company;
        $customer = Customer::find($selectedCustomer);
        if ( isset($selectedCustomer) && $selectedType == "Outstanding Invoices") {
            $invoices = Invoice::where('customer_id', $selectedCustomer)
            ->where('authorization', 'approved')
            ->where('status', 'Unpaid')
            ->orWhere('customer_id', $selectedCustomer)
            ->where('authorization','approved')
            ->where('status', 'Partial')->get();
            
            $data = [
                'selectedCustomer' => $selectedCustomer,
                'selectedType' => $selectedType,
                'from' => $from,
                'to' => $to,
                'invoices' => $invoices,
                'company' => $company,
                'customer' => $customer,
            ];
    
        }elseif ( isset($selectedCustomer) && $selectedType == "Account Activity") {
            $activity = [];
            if (isset($from) && isset($to)) {
                $activity = $this->accountActivityData($selectedCustomer, $from, $to);
            }
            $data = [
                'selectedCustomer' => $selectedCustomer,
                'selectedType' => $selectedType,
                'from' => $from,
                'to' => $to,
                'invoices' => $activity['results'] ?? null,
                'results' => $activity['results'] ?? null,
                'openingBalances' => $activity['openingBalances'] ?? [],
                'closingBalances' => $activity['closingBalances'] ?? [],
                'company' => $company,
                'customer' => $customer,
            ];

        }

        $pdf = PDF::loadView('customer_statements.customer_statement', $data);

        $fileName = 'customer_statement_' . time() . '.pdf';
        $filePath = 'myfiles/documents/' . $fileName;
        Storage::disk('local')->put($filePath, $pdf->output());

        return $pdf->download('customer_statement.pdf');

    }

    

    public function sendEmailWithAttachment($data, $filePath)
    {       $customer = $data['customer'];
        if ($customer->email) {
            Mail::to($customer->email)->send(new SendCustomerStatementMail($data, $filePath));
        }
    
    }

    public function customerStatementsEmail($selectedCustomer = null, $selectedType = null, $from = null, $to = null){
        $company = Auth::user()->employee->company;
        $customer = Customer::find($selectedCustomer);
        if ( isset($selectedCustomer) && $selectedType == "Outstanding Invoices") {
            $invoices = Invoice::where('customer_id', $selectedCustomer)
            ->where('authorization', 'approved')
            ->where('status', 'Unpaid')
            ->orWhere('customer_id', $selectedCustomer)
            ->where('authorization','approved')
            ->where('status', 'Partial')->get();
            
            $data = [
                'selectedCustomer' => $selectedCustomer,
                'selectedType' => $selectedType,
                'from' => $from,
                'to' => $to,
                'invoices' => $invoices,
                'company' => $company,
                'customer' => $customer,
            ];
    
        }elseif ( isset($selectedCustomer) && $selectedType == "Account Activity") {
            $activity = [];
            if (isset($from) && isset($to)) {
                $activity = $this->accountActivityData($selectedCustomer, $from, $to);
            }
            $data = [
                'selectedCustomer' => $selectedCustomer,
                'selectedType' => $selectedType,
                'from' => $from,
                'to' => $to,
                'invoices' => $activity['results'] ?? null,
                'results' => $activity['results'] ?? null,
                'openingBalances' => $activity['openingBalances'] ?? [],
                'closingBalances' => $activity['closingBalances'] ?? [],
                'company' => $company,
                'customer' => $customer,
            ];

        }

        $pdf = PDF::loadView('customer_statements.customer_statement', $data);

        $fileName = 'customer_statement_' . time() . '.pdf';
        $filePath = 'myfiles/documents/' . $fileName;
        Storage::disk('local')->put($filePath, $pdf->output());

        $this->sendEmailWithAttachment($data, $filePath);

        Session::flash('success','Customer Statement Send Successfully');
        return redirect()->back();
        // return response()->json(['message' => 'PDF generated and email sent.']);

    }
    public function rejected()
    {
        return view('invoices.rejected');
    }
    public function pending()
    {
        return view('invoices.pending');
    }
    public function approved()
    {
        return view('invoices.approved');
    }
    
    public function deleted()
    {
        return view('invoices.deleted');
    }

    public function previewClassic($id){
        $invoice = Invoice::find($id);
        $company = $invoice->company;
        $invoice_items = $invoice->invoice_items;
        return view('invoices.classic')->with([
            'invoice' => $invoice,
            'company' => $company,
            'invoice_items' => $invoice_items,
            ]);
    }

    public function previewModern($id){
        $invoice = Invoice::find($id);
        $company = $invoice->company;
        $invoice_items = $invoice->invoice_items;
        return view('invoices.modern')->with([
            'invoice' => $invoice,
            'company' => $company,
            'invoice_items' => $invoice_items,
            ]);
    }
    
    public function previewTransport($id){
        $invoice = Invoice::find($id);
        $company = $invoice->company;
        $invoice_items = $invoice->invoice_items;
        return view('invoices.transport')->with([
            'invoice' => $invoice,
            'company' => $company,
            'invoice_items' => $invoice_items,
            ]);
    }

    public function print($id){
        $invoice = Invoice::find($id);
        $company = $invoice->company;
        $invoice_items = $invoice->invoice_items;
        return view('invoices.print')->with([
            'invoice' => $invoice,
            'company' => $company,
            'invoice_items' => $invoice_items,

        ]);
    }

    public function generatePDF(Invoice $invoice){
        $company = $invoice->company;
        $invoice_items = $invoice->invoice_items;
        $data = [
            'invoice' => $invoice,
            'company' => $company,
            'invoice_items' => $invoice_items,
        ];
        $pdf = PDF::loadView('invoices.invoice', $data);

        return $pdf->download('invoice.pdf');

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('invoices.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Invoice  $invoice
     * @return \Illuminate\Http\Response
     */
    public function show(Invoice $invoice)
    {
        return view('invoices.show')->with('invoice', $invoice);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Invoice  $invoice
     * @return \Illuminate\Http\Response
     */
    public function edit(Invoice $invoice)
    {
        return view('invoices.edit')->with('invoice', $invoice);

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Invoice  $invoice
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Invoice $invoice)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Invoice  $invoice
     * @return \Illuminate\Http\Response
     */
    public function destroy(Invoice $invoice)
    {
        $bills = $invoice->bills;
        if (isset($bills)) {
            foreach ($bills as $bill) {
                $bill_expenses = $bill->bill_expenses;
                if (isset($bill_expenses)) {
                    foreach ($bill_expenses as $bill_expense) {
                        $bill_expense->delete();
                    }
                }
                $bill->delete();
            }
        }
        $invoice_items = $invoice->invoice_items;
        if ($invoice_items->count()>0) {
            foreach ($invoice_items as $invoice_item) {
                $invoice_item->delete();
            }
        }
        $payments = $invoice->payments;
        if ($payments->count()>0) {
            foreach ($payments as $payment) {
                if ($payment) {
                    $cashflow = $payment->cash_flow;

                    if (isset($cashflow)) {
                        $cashflow->delete();
                    }

                    $denominations = $payment->denominations;
                    if (isset($denominations)) {
                        foreach ($denominations as $denomination) {
                            $denomination->delete();
                        }
                    }
                    $receipt = $payment->receipt;
                    if (isset($receipt)) {
                        $receipt->delete();
                    }
                }
                $payment->delete();
            }
        }
        $invoice->delete();
        Session::flash('success','Invoice Deleted Successfully!!');
        return redirect()->back();
    }
}
