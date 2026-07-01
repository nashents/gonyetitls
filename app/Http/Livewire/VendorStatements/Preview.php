<?php

namespace App\Http\Livewire\VendorStatements;

use App\Models\Bill;
use App\Models\Currency;
use App\Models\Vendor;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Services\VendorLedgerService;
use Carbon\Carbon;

class Preview extends Component
{
    public $selectedVendor;
    public $selectedType;
    public $from;
    public $to;

    public $vendor;
    public $company;

    public $bills               = null;
    public $results             = null;
    public $statementByCurrency = [];

    public function mount($selectedVendor, $selectedType, $from = null, $to = null)
    {
        $this->selectedVendor = $selectedVendor;
        $this->selectedType   = $selectedType;
        $this->from           = $from;
        $this->to             = $to;

        $this->vendor  = Vendor::findOrFail($selectedVendor);
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
        $this->bills = Bill::with(['vendor', 'currency', 'payments'])
            ->where('vendor_id', $this->selectedVendor)
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
            $activity = $service->activity((int) $this->selectedVendor, $currencyId, $from, $to);

            if ($activity->isEmpty()) {
                continue;
            }

            $this->results = $this->results->merge($activity);

            $totalBilled = $activity->where('transaction_type', 'bill')->sum('amount');
            $totalPaid   = $activity->where('transaction_type', 'payment')->sum('amount');

            $this->statementByCurrency[$currencyId] = [
                'currency'        => Currency::find($currencyId),
                'opening_balance' => $service->openingBalance((int) $this->selectedVendor, $currencyId, $from),
                'closing_balance' => $service->closingBalance((int) $this->selectedVendor, $currencyId, $to),
                'total_billed'    => $totalBilled,
                'total_paid'      => $totalPaid,
                'results'         => $activity,
            ];
        }

        $this->bills = $this->results->isNotEmpty() ? $this->results : collect();
    }

    public function render()
    {
        return view('livewire.vendor-statements.preview', [
            'bills'               => $this->bills,
            'results'             => $this->results,
            'statementByCurrency' => $this->statementByCurrency,
        ]);
    }
}
