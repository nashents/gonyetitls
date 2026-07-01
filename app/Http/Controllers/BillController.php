<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\Vendor;
use App\Models\Currency;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendVendorStatementMail;
use App\Http\Requests\StoreBillRequest;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\UpdateBillRequest;
use App\Services\VendorLedgerService;

class BillController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('bills.index');
    }

            public function vendorStatements()
    {
        return view('vendor_statements.index');
    }
    
    public function vendorStatementsPreview($selectedVendor = null, $selectedType = null, $from = null, $to = null){
      
        return view('vendor_statements.preview')->with([
            'selectedVendor' => $selectedVendor,
            'selectedType' => $selectedType,
            'from' => $from,
            'to' => $to,
            ]);
    }

    /**
     * Account Activity figures derived live from the transaction ledger
     * (approved bills, payments) for every currency the vendor has been
     * billed in. Never reads a stored balance column.
     */
    private function accountActivityData($selectedVendor, $from, $to): array
    {
        $service = app(VendorLedgerService::class);
        $currencies = Currency::all();

        $openingBalances = [];
        $closingBalances = [];
        $results = collect();

        foreach ($currencies as $currency) {
            $openingBalances[$currency->id] = $service->openingBalance((int) $selectedVendor, $currency->id, $from);
            $closingBalances[$currency->id] = $service->closingBalance((int) $selectedVendor, $currency->id, $to);
            $results = $results->merge($service->activity((int) $selectedVendor, $currency->id, $from, $to));
        }

        return [
            'results' => $results,
            'openingBalances' => $openingBalances,
            'closingBalances' => $closingBalances,
        ];
    }

    public function vendorStatementsPrint($selectedVendor = null, $selectedType = null, $from = null, $to = null){
        $company = Auth::user()->employee->company;
        $vendor = Vendor::find($selectedVendor);
        if ( isset($selectedVendor) && $selectedType == "Outstanding Bills") {

            $bills = Bill::where('vendor_id', $selectedVendor)
            ->where('authorization', 'approved')
            ->where('status', 'Unpaid')
            ->orWhere('vendor_id', $selectedVendor)
            ->where('authorization','approved')
            ->where('status', 'Partial')->get();

            return view('vendor_statements.print')->with([
                'selectedVendor' => $selectedVendor,
                'selectedType' => $selectedType,
                'from' => $from,
                'to' => $to,
                'bills' => $bills,
                'company' => $company,
                'vendor' => $vendor,
                ]);
    
        }elseif ( isset($selectedVendor) && $selectedType == "Account Activity") {
            $activity = [];
            if (isset($from) && isset($to)) {
                $activity = $this->accountActivityData($selectedVendor, $from, $to);
            }
            return view('vendor_statements.print')->with([
                'selectedVendor' => $selectedVendor,
                'selectedType' => $selectedType,
                'from' => $from,
                'to' => $to,
                'bills' => $activity['results'] ?? null,
                'results' => $activity['results'] ?? null,
                'openingBalances' => $activity['openingBalances'] ?? [],
                'closingBalances' => $activity['closingBalances'] ?? [],
                'company' => $company,
                'vendor' => $vendor,
                ]);
        }


    }

    public function vendorStatementsPDF($selectedVendor = null, $selectedType = null, $from = null, $to = null){
        $company = Auth::user()->employee->company;
        $vendor = Vendor::find($selectedVendor);
        if ( isset($selectedVendor) && $selectedType == "Outstanding Bills") {
            $bills = Bill::where('vendor_id', $selectedVendor)
            ->where('authorization', 'approved')
            ->where('status', 'Unpaid')
            ->orWhere('vendor_id', $selectedVendor)
            ->where('authorization','approved')
            ->where('status', 'Partial')->get();
            
            $data = [
                'selectedVendor' => $selectedVendor,
                'selectedType' => $selectedType,
                'from' => $from,
                'to' => $to,
                'bills' => $bills,
                'company' => $company,
                'vendor' => $vendor,
            ];
    
        }elseif ( isset($selectedVendor) && $selectedType == "Account Activity") {
            $activity = [];
            if (isset($from) && isset($to)) {
                $activity = $this->accountActivityData($selectedVendor, $from, $to);
            }
            $data = [
                'selectedVendor' => $selectedVendor,
                'selectedType' => $selectedType,
                'from' => $from,
                'to' => $to,
                'bills' => $activity['results'] ?? null,
                'results' => $activity['results'] ?? null,
                'openingBalances' => $activity['openingBalances'] ?? [],
                'closingBalances' => $activity['closingBalances'] ?? [],
                'company' => $company,
                'vendor' => $vendor,
            ];

        }

        $pdf = PDF::loadView('vendor_statements.vendor_statement', $data);

        $fileName = 'vendor_statement_' . time() . '.pdf';
        $filePath = 'myfiles/documents/' . $fileName;
        Storage::disk('local')->put($filePath, $pdf->output());

        return $pdf->download('vendor_statement.pdf');

    }

    

    public function sendEmailWithAttachment($data, $filePath)
    {       $vendor = $data['vendor'];
        if ($vendor->email) {
            Mail::to($vendor->email)->send(new SendVendorStatementMail($data, $filePath));
        }
    
    }

    public function vendorStatementsEmail($selectedVendor = null, $selectedType = null, $from = null, $to = null){
        $company = Auth::user()->employee->company;
        $vendor = Vendor::find($selectedVendor);
        if ( isset($selectedVendor) && $selectedType == "Outstanding Bills") {
            $bills = Bill::where('vendor_id', $selectedVendor)
            ->where('authorization', 'approved')
            ->where('status', 'Unpaid')
            ->orWhere('vendor_id', $selectedVendor)
            ->where('authorization','approved')
            ->where('status', 'Partial')->get();
            
            $data = [
                'selectedVendor' => $selectedVendor,
                'selectedType' => $selectedType,
                'from' => $from,
                'to' => $to,
                'bills' => $bills,
                'company' => $company,
                'vendor' => $vendor,
            ];
    
        }elseif ( isset($selectedVendor) && $selectedType == "Account Activity") {
            $activity = [];
            if (isset($from) && isset($to)) {
                $activity = $this->accountActivityData($selectedVendor, $from, $to);
            }
            $data = [
                'selectedVendor' => $selectedVendor,
                'selectedType' => $selectedType,
                'from' => $from,
                'to' => $to,
                'bills' => $activity['results'] ?? null,
                'results' => $activity['results'] ?? null,
                'openingBalances' => $activity['openingBalances'] ?? [],
                'closingBalances' => $activity['closingBalances'] ?? [],
                'company' => $company,
                'vendor' => $vendor,
            ];

        }

        $pdf = PDF::loadView('vendor_statements.vendor_statement', $data);

        $fileName = 'vendor_statement_' . time() . '.pdf';
        $filePath = 'myfiles/documents/' . $fileName;
        Storage::disk('local')->put($filePath, $pdf->output());

        $this->sendEmailWithAttachment($data, $filePath);

        Session::flash('success','Vendor Statement Send Successfully');
        return redirect()->back();
        // return response()->json(['message' => 'PDF generated and email sent.']);

    }

    public function rejected()
    {
        return view('bills.rejected');
    }
    public function pending()
    {
        return view('bills.pending');
    }
    public function approved()
    {
        return view('bills.approved');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('bills.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreBillRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreBillRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Bill  $bill
     * @return \Illuminate\Http\Response
     */
    public function show(Bill $bill)
    {
        return view('bills.show')->with('bill',$bill);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Bill  $bill
     * @return \Illuminate\Http\Response
     */
    public function edit(Bill $bill)
    {
        return view('bills.edit')->with('bill',$bill);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateBillRequest  $request
     * @param  \App\Models\Bill  $bill
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateBillRequest $request, Bill $bill)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Bill  $bill
     * @return \Illuminate\Http\Response
     */
    public function destroy(Bill $bill)
    {
        $bill_expenses = $bill->bill_expenses;
        if (isset($bill_expenses)) {
            foreach ($bill_expenses as $bill_expense) {
                $bill_expense->delete();
            }
        }
     
        $payments = $bill->payments;
        if (isset($payments)) {
            foreach ($payments as $payment) {
                $payment->delete();
            }
        }
       

        $bill->delete();
        Session::flash('success','Bill Deleted Successfully!!');
        return redirect()->back();
    }
}
