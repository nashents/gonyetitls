<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\Vendor;
use App\Models\Transporter;
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

    public function deleted()
    {
        return view('bills.deleted');
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
            'party' => $this->statementParty(),
            ]);
    }

    /** 'vendor' (default) or 'transporter', from the ?party= query string. */
    private function statementParty(): string
    {
        return request()->query('party') === VendorLedgerService::PARTY_TRANSPORTER
            ? VendorLedgerService::PARTY_TRANSPORTER
            : VendorLedgerService::PARTY_VENDOR;
    }

    /**
     * Account Activity figures derived live from the transaction ledger
     * (approved bills, payments) for every currency the vendor/transporter
     * has been billed in. Never reads a stored balance column.
     */
    private function accountActivityData($selectedVendor, $from, $to, string $party = VendorLedgerService::PARTY_VENDOR): array
    {
        $service = app(VendorLedgerService::class);
        $currencies = Currency::all();

        $openingBalances = [];
        $closingBalances = [];
        $results = collect();

        foreach ($currencies as $currency) {
            $openingBalances[$currency->id] = $service->openingBalance((int) $selectedVendor, $currency->id, $from, $party);
            $closingBalances[$currency->id] = $service->closingBalance((int) $selectedVendor, $currency->id, $to, $party);
            $results = $results->merge($service->activity((int) $selectedVendor, $currency->id, $from, $to, $party));
        }

        return [
            'results' => $results,
            'openingBalances' => $openingBalances,
            'closingBalances' => $closingBalances,
        ];
    }

    /**
     * View data shared by the statement print, PDF and email. $vendor holds
     * the counterparty model (Vendor or Transporter) and $partyColumn the
     * bills/payments column it is keyed on, so the templates serve both.
     */
    private function statementData($selectedVendor, $selectedType, $from, $to): array
    {
        $party = $this->statementParty();
        $partyColumn = VendorLedgerService::partyColumn($party);
        $vendor = $party === VendorLedgerService::PARTY_TRANSPORTER
            ? Transporter::find($selectedVendor)
            : Vendor::find($selectedVendor);

        $data = [
            'selectedVendor' => $selectedVendor,
            'selectedType' => $selectedType,
            'from' => $from,
            'to' => $to,
            'company' => Auth::user()->employee->company,
            'vendor' => $vendor,
            'party' => $party,
            'partyColumn' => $partyColumn,
        ];

        if (isset($selectedVendor) && $selectedType == "Outstanding Bills") {
            $data['bills'] = Bill::where($partyColumn, $selectedVendor)
                ->where('authorization', 'approved')
                ->whereIn('status', ['Unpaid', 'Partial'])
                ->get();
        } elseif (isset($selectedVendor) && $selectedType == "Account Activity") {
            $activity = [];
            if (isset($from) && isset($to)) {
                $activity = $this->accountActivityData($selectedVendor, $from, $to, $party);
            }
            $data['bills'] = $activity['results'] ?? null;
            $data['results'] = $activity['results'] ?? null;
            $data['openingBalances'] = $activity['openingBalances'] ?? [];
            $data['closingBalances'] = $activity['closingBalances'] ?? [];
        }

        return $data;
    }

    public function vendorStatementsPrint($selectedVendor = null, $selectedType = null, $from = null, $to = null){
        return view('vendor_statements.print')->with($this->statementData($selectedVendor, $selectedType, $from, $to));
    }

    public function vendorStatementsPDF($selectedVendor = null, $selectedType = null, $from = null, $to = null){
        $data = $this->statementData($selectedVendor, $selectedType, $from, $to);

        $pdf = PDF::loadView('vendor_statements.vendor_statement', $data);

        $fileName = $data['party'] . '_statement_' . time() . '.pdf';
        $filePath = 'myfiles/documents/' . $fileName;
        Storage::disk('local')->put($filePath, $pdf->output());

        return $pdf->download($data['party'] . '_statement.pdf');

    }

    

    public function sendEmailWithAttachment($data, $filePath)
    {       $vendor = $data['vendor'];
        if ($vendor->email) {
            Mail::to($vendor->email)->send(new SendVendorStatementMail($data, $filePath));
        }
    
    }

    public function vendorStatementsEmail($selectedVendor = null, $selectedType = null, $from = null, $to = null){
        $data = $this->statementData($selectedVendor, $selectedType, $from, $to);

        if (! optional($data['vendor'])->email) {
            Session::flash('error', ucfirst($data['party']).' has no email address - add one before sending a statement.');
            return redirect()->back();
        }

        $pdf = PDF::loadView('vendor_statements.vendor_statement', $data);

        $fileName = $data['party'] . '_statement_' . time() . '.pdf';
        $filePath = 'myfiles/documents/' . $fileName;
        Storage::disk('local')->put($filePath, $pdf->output());

        $this->sendEmailWithAttachment($data, $filePath);

        Session::flash('success', ucfirst($data['party']).' Statement Sent Successfully');
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
