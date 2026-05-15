<?php

namespace App\Http\Livewire\VendorStatements;

use App\Models\Bill;
use App\Models\Currency;
use App\Models\Vendor;
use App\Models\Payment;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
        $from = Carbon::parse($this->from)->startOfDay();
        $to   = Carbon::parse($this->to)->endOfDay();

        // Bills FIRST — its aliases (bill_date as transaction_date) drive the UNION column names
        $billsQuery = DB::table('bills')
            ->selectRaw("
                'bill' as transaction_type,
                bill_number as number,
                currency_id,
                created_at,
                bill_date as transaction_date,
                total as amount,
                balance,
                accrual_balance
            ")
            ->where('vendor_id', $this->selectedVendor)
            ->where('authorization', 'approved')
            ->whereNull('deleted_at')
            ->whereBetween('bill_date', [$from, $to]);

        $paymentsQuery = DB::table('payments')
            ->selectRaw("
                'payment' as transaction_type,
                payment_number as number,
                currency_id,
                created_at,
                date as transaction_date,
                amount,
                balance,
                accrual_balance
            ")
            ->where('vendor_id', $this->selectedVendor)
            ->whereNull('deleted_at')
            ->whereBetween('date', [$from, $to]);

        $this->results = $billsQuery
            ->union($paymentsQuery)
            ->get()
            ->sortBy([
                ['transaction_date', 'asc'],
                ['created_at', 'asc'],
            ]);

        $currencyIds = $this->results->pluck('currency_id')->unique();

        foreach ($currencyIds as $currencyId) {
            $ledger = $this->makeLedgerQuery($currencyId);

            $openingBalance = DB::query()
                ->fromSub($ledger, 'l')
                ->where('date', '<', $from)
                ->orderBy('date', 'desc')
                ->orderBy('created_at', 'desc')
                ->orderBy('pay_first', 'asc')
                ->orderBy('id', 'desc')
                ->value('accrual_balance') ?? 0;

            $closingBalance = DB::query()
                ->fromSub($ledger, 'l')
                ->where('date', '<=', $to)
                ->orderBy('date', 'desc')
                ->orderBy('created_at', 'desc')
                ->orderBy('pay_first', 'asc')
                ->orderBy('id', 'desc')
                ->value('accrual_balance') ?? 0;

            $totalBilled = Bill::where('vendor_id', $this->selectedVendor)
                ->where('authorization', 'approved')
                ->where('currency_id', $currencyId)
                ->whereBetween('bill_date', [$from, $to])
                ->whereNull('deleted_at')
                ->whereRaw('total REGEXP "^-?[0-9]+(\\.[0-9]+)?$"')
                ->sum('total');

            $totalPaid = Payment::where('vendor_id', $this->selectedVendor)
                ->where('currency_id', $currencyId)
                ->whereBetween('date', [$from, $to])
                ->whereNull('deleted_at')
                ->whereRaw('amount REGEXP "^-?[0-9]+(\\.[0-9]+)?$"')
                ->sum('amount');

            $this->statementByCurrency[$currencyId] = [
                'currency'        => Currency::find($currencyId),
                'opening_balance' => $openingBalance,
                'closing_balance' => $closingBalance,
                'total_billed'    => $totalBilled,
                'total_paid'      => $totalPaid,
                'results'         => $this->results->where('currency_id', $currencyId)->values(),
            ];
        }

        $this->bills = $this->results->isNotEmpty() ? $this->results : collect();
    }

    private function makeLedgerQuery(int $currencyId)
    {
        $billLedger = Bill::query()
            ->selectRaw('bill_date as date, created_at, 1 as pay_first, CAST(accrual_balance AS DECIMAL(20,2)) AS accrual_balance, id')
            ->where('authorization', 'approved')
            ->where('vendor_id', $this->selectedVendor)
            ->where('currency_id', $currencyId)
            ->whereNotNull('accrual_balance')
            ->whereRaw('accrual_balance REGEXP "^-?[0-9]+(\\.[0-9]+)?$"')
            ->whereNull('deleted_at');

        $paymentLedger = Payment::query()
            ->selectRaw('date, created_at, 0 as pay_first, CAST(accrual_balance AS DECIMAL(20,2)) AS accrual_balance, id')
            ->where('vendor_id', $this->selectedVendor)
            ->where('currency_id', $currencyId)
            ->whereNotNull('accrual_balance')
            ->whereRaw('accrual_balance REGEXP "^-?[0-9]+(\\.[0-9]+)?$"')
            ->whereNull('deleted_at');

        return $billLedger->unionAll($paymentLedger);
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
