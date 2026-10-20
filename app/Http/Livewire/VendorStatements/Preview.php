<?php

namespace App\Http\Livewire\VendorStatements;

use App\Models\Bill;
use App\Models\Currency;
use App\Models\Vendor;
use App\Models\Transporter;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Services\VendorLedgerService;
use App\Services\Accounting\TransporterSettlementService;
use Carbon\Carbon;

class Preview extends Component
{
    public $party = VendorLedgerService::PARTY_VENDOR;
    public $partyColumn = 'vendor_id';
    public $selectedVendor;
    public $selectedType;
    public $from;
    public $to;

    public $vendor;
    public $company;

    public $bills               = null;
    public $results             = null;
    public $statementByCurrency = [];

    public function mount($selectedVendor, $selectedType, $from = null, $to = null, $party = VendorLedgerService::PARTY_VENDOR)
    {
        $this->party          = $party === VendorLedgerService::PARTY_TRANSPORTER ? $party : VendorLedgerService::PARTY_VENDOR;
        $this->partyColumn    = VendorLedgerService::partyColumn($this->party);
        $this->selectedVendor = $selectedVendor;
        $this->selectedType   = $selectedType;
        $this->from           = $from;
        $this->to             = $to;

        $this->vendor  = $this->party === VendorLedgerService::PARTY_TRANSPORTER
            ? Transporter::findOrFail($selectedVendor)
            : Vendor::findOrFail($selectedVendor);
        $this->company = Auth::user()->employee->company;

        $this->loadStatement();
    }

    private function loadStatement()
    {
        if ($this->selectedType === 'Outstanding Bills') {
            $this->loadOutstandingBills();
        } elseif ($this->selectedType === 'Account Activity' && $this->from && $this->to) {
            $this->loadAccountActivity();
        }
    }

    private function loadOutstandingBills()
    {
        $this->bills = Bill::with(['vendor', 'transporter', 'currency', 'payments'])
            ->where($this->partyColumn, $this->selectedVendor)
            ->where('authorization', 'approved')
            ->where(function ($q) {
                $q->where('status', 'Unpaid')
                  ->orWhere('status', 'Partial');
            })
            ->orderBy('due_date')
            ->get();
    }

    private function loadAccountActivity()
    {
        $from = Carbon::parse($this->from)->startOfDay()->toDateString();
        $to   = Carbon::parse($this->to)->endOfDay()->toDateString();

        $service = app(VendorLedgerService::class);

        $currencyIds = Currency::all()->pluck('id');

        $this->results = collect();
        $this->statementByCurrency = [];

        foreach ($currencyIds as $currencyId) {
            $activity = $service->activity((int) $this->selectedVendor, $currencyId, $from, $to, $this->party);

            if ($activity->isEmpty()) {
                continue;
            }

            $this->results = $this->results->merge($activity);

            $totalBilled = $activity->where('transaction_type', 'bill')->sum('amount');
            $totalPaid   = $activity->where('transaction_type', 'payment')->sum('amount');

            $this->statementByCurrency[$currencyId] = [
                'currency'        => Currency::find($currencyId),
                'opening_balance' => $service->openingBalance((int) $this->selectedVendor, $currencyId, $from, $this->party),
                'closing_balance' => $service->closingBalance((int) $this->selectedVendor, $currencyId, $to, $this->party),
                'total_billed'    => $totalBilled,
                'total_paid'      => $totalPaid,
                'results'         => $activity,
            ];
        }

        $this->bills = $this->results->isNotEmpty() ? $this->results : collect();
    }

    public function render()
    {
        // Trip Settlement is transporter-only and built per render (holds models Livewire can't serialise).
        $settlement = $this->selectedType === 'Trip Settlement' && $this->party === VendorLedgerService::PARTY_TRANSPORTER && $this->from && $this->to
            ? app(TransporterSettlementService::class)->build($this->vendor, Carbon::parse($this->from)->toDateString(), Carbon::parse($this->to)->toDateString())
            : [];

        return view('livewire.vendor-statements.preview', [
            'bills'               => $this->bills,
            'results'             => $this->results,
            'statementByCurrency' => $this->statementByCurrency,
            'settlement'          => $settlement,
        ]);
    }
}
