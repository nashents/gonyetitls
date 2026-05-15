<?php

namespace App\Http\Livewire\VendorStatements;

use App\Models\Bill;
use App\Models\Currency;
use App\Models\Vendor;
use App\Exports\VendorStatementExport;
use Livewire\Component;
use Maatwebsite\Excel\Excel;
use Illuminate\Support\Facades\DB;

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
       
       $billsQuery = DB::table('bills')
        ->selectRaw('
        bill_number as number,
        currency_id,
        bill_date as transaction_date,
        total as amount,
        balance,
        accrual_balance,
        created_at')
        ->where('vendor_id', $this->selectedVendor)
        ->where('authorization', 'approved')
        ->whereNull('deleted_at')
        ->whereBetween('bill_date', [$this->from, $this->to]);

        $this->results = DB::table('payments')
            ->select(
                'payment_number as number',
                'currency_id',
                'date as transaction_date',
                'amount',
                'balance',
                'accrual_balance',
                'created_at'
            )
            ->where('vendor_id', $this->selectedVendor)
            ->whereNull('deleted_at')
            ->whereBetween('date', [$this->from, $this->to])
            ->union($billsQuery)
            ->get()
            ->sortBy([
                ['transaction_date', 'asc'],
                ['created_at', 'asc'],
            ]);

        // Build per-currency statement data (opening/closing from accrual_balance snapshots)
        $currencyIds = $this->results->pluck('currency_id')->unique();

        $this->statementByCurrency = [];

        foreach ($currencyIds as $currencyId) {
            // Last approved bill before $from — snapshot opening balance
            $openingBill = Bill::where('vendor_id', $this->selectedVendor)
                ->where('authorization', 'approved')
                ->where('currency_id', $currencyId)
                ->where('bill_date', '<', $this->from)
                ->whereNotNull('accrual_balance')
                ->whereRaw('accrual_balance REGEXP "^-?[0-9]+(\\.[0-9]+)?$"')
                ->orderBy('bill_date', 'desc')
                ->orderBy('id', 'desc')
                ->first();

            // Last approved bill within range — snapshot closing balance
            $closingBill = Bill::where('vendor_id', $this->selectedVendor)
                ->where('authorization', 'approved')
                ->where('currency_id', $currencyId)
                ->whereBetween('bill_date', [$this->from, $this->to])
                ->whereNotNull('accrual_balance')
                ->whereRaw('accrual_balance REGEXP "^-?[0-9]+(\\.[0-9]+)?$"')
                ->orderBy('bill_date', 'desc')
                ->orderBy('id', 'desc')
                ->first();

            $this->statementByCurrency[$currencyId] = [
                'currency'        => Currency::find($currencyId),
                'opening_balance' => $openingBill?->accrual_balance,
                'closing_balance' => $closingBill?->accrual_balance,
                'results'         => $this->results->where('currency_id', $currencyId)->values(),
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
