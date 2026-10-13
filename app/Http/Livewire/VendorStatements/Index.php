<?php

namespace App\Http\Livewire\VendorStatements;

use App\Models\Bill;
use App\Models\Currency;
use App\Models\Vendor;
use App\Models\Transporter;
use App\Exports\VendorStatementExport;
use Livewire\Component;
use Maatwebsite\Excel\Excel;
use App\Services\VendorLedgerService;

class Index extends Component
{
    public $from;
    public $to;
    // 'vendor' or 'transporter' - which counterparty list the statement is for.
    // selectedVendor/$vendor then hold that party's id/model.
    public $party = VendorLedgerService::PARTY_VENDOR;
    public $selectedVendor;
    public $selectedType;

    public $vendor;
    public $vendors;
    public $transporters;

    public $bills               = null;
    public $results             = null;
    public $statementByCurrency = [];

    public function mount()
    {
        $this->vendors = Vendor::where('status', true)->orderBy('name')->get();
        $this->transporters = Transporter::orderBy('name')->get();
    }

    // ─── Watchers ─────────────────────────────────────────────────────────────

    public function updatedParty()
    {
        $this->selectedVendor = null;
        $this->vendor = null;
        $this->resetStatement();
    }

    public function updatedSelectedVendor($id)
    {
        $this->vendor = $id ? $this->partyModel()::find($id) : null;
        $this->resetStatement();
        $this->generateStatement();
    }

    public function updatedSelectedType()
    {
        $this->resetStatement();
        $this->generateStatement();
    }

    public function updatedFrom()
    {
        $this->resetStatement();
        $this->generateStatement();
    }

    public function updatedTo()
    {
        $this->resetStatement();
        $this->generateStatement();
    }

    // ─── Statement Generation ─────────────────────────────────────────────────

    public function generateStatement()
    {
        if (! $this->selectedVendor || ! $this->selectedType) {
            return;
        }

        if ($this->selectedType === 'Outstanding Bills') {
            $this->loadOutstandingBills();
        } elseif ($this->selectedType === 'Account Activity' && $this->from && $this->to) {
            $this->loadAccountActivity();
        }
    }

    private function loadOutstandingBills()
    {
        $this->bills = Bill::with(['vendor', 'transporter', 'currency', 'payments'])
            ->where(VendorLedgerService::partyColumn($this->party), $this->selectedVendor)
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
        $service = app(VendorLedgerService::class);

        // Currencies the vendor has approved bills or payments in, within range
        $currencyIds = Currency::all()->pluck('id');

        $this->results = collect();
        $this->statementByCurrency = [];

        foreach ($currencyIds as $currencyId) {
            $activity = $service->activity((int) $this->selectedVendor, $currencyId, $this->from, $this->to, $this->party);

            if ($activity->isEmpty()) {
                continue;
            }

            $this->results = $this->results->merge($activity);

            $this->statementByCurrency[$currencyId] = [
                'currency'        => Currency::find($currencyId),
                'opening_balance' => $service->openingBalance((int) $this->selectedVendor, $currencyId, $this->from, $this->party),
                'closing_balance' => $service->closingBalance((int) $this->selectedVendor, $currencyId, $this->to, $this->party),
                'results'         => $activity,
            ];
        }

        // $this->bills drives the "has results" check in the view
        $this->bills = $this->results->isNotEmpty() ? $this->results : collect();
    }

    // ─── Export ───────────────────────────────────────────────────────────────

    public function exportVendorStatementExcel(Excel $excel)
    {
        return $excel->download(
            new VendorStatementExport($this->selectedType, $this->selectedVendor, $this->from, $this->to, $this->party),
            $this->party.'_statement.xlsx'
        );
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    private function partyModel(): string
    {
        return $this->party === VendorLedgerService::PARTY_TRANSPORTER ? Transporter::class : Vendor::class;
    }

    private function resetStatement()
    {
        $this->bills               = null;
        $this->results             = null;
        $this->statementByCurrency = [];
    }

    // ─── Render ───────────────────────────────────────────────────────────────

    public function render()
    {
        return view('livewire.vendor-statements.index', [
            'bills' => $this->bills,
        ]);
    }
}
