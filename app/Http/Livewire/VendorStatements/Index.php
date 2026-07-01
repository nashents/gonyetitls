<?php

namespace App\Http\Livewire\VendorStatements;

use App\Models\Bill;
use App\Models\Currency;
use App\Models\Vendor;
use App\Exports\VendorStatementExport;
use Livewire\Component;
use Maatwebsite\Excel\Excel;
use App\Services\VendorLedgerService;

class Index extends Component
{
    public $from;
    public $to;
    public $selectedVendor;
    public $selectedType;

    public $vendor;
    public $vendors;

    public $bills               = null;
    public $results             = null;
    public $statementByCurrency = [];

    public function mount()
    {
        $this->vendors = Vendor::where('status', true)->orderBy('name')->get();
    }

    // ─── Watchers ─────────────────────────────────────────────────────────────

    public function updatedSelectedVendor($id)
    {
        $this->vendor = $id ? Vendor::find($id) : null;
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
        $service = app(VendorLedgerService::class);

        // Currencies the vendor has approved bills or payments in, within range
        $currencyIds = Currency::all()->pluck('id');

        $this->results = collect();
        $this->statementByCurrency = [];

        foreach ($currencyIds as $currencyId) {
            $activity = $service->activity((int) $this->selectedVendor, $currencyId, $this->from, $this->to);

            if ($activity->isEmpty()) {
                continue;
            }

            $this->results = $this->results->merge($activity);

            $this->statementByCurrency[$currencyId] = [
                'currency'        => Currency::find($currencyId),
                'opening_balance' => $service->openingBalance((int) $this->selectedVendor, $currencyId, $this->from),
                'closing_balance' => $service->closingBalance((int) $this->selectedVendor, $currencyId, $this->to),
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
            new VendorStatementExport($this->selectedType, $this->selectedVendor, $this->from, $this->to),
            'vendor_statement.xlsx'
        );
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

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
