<?php

namespace App\Http\Livewire\CustomerStatements;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Currency;
use Livewire\Component;
use App\Models\Customer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class Preview extends Component
{
    public $selectedCustomer;
    public $customer;
    public $selectedType;
    public $from;
    public $to;
    public $company;
    protected $invoices;
    public $invoice;
    public $results;
    public $result;
    public $closingBalances = [];
    public $openingBalances = [];

    public function mount($selectedCustomer, $selectedType, $from = null, $to = null)
    {
        $this->selectedCustomer = $selectedCustomer;
        $this->customer         = Customer::find($this->selectedCustomer);
        $this->selectedType     = $selectedType;
        $this->from             = $from;
        $this->to               = $to;
        $this->company          = Auth::user()->employee->company;
    }

    /**
     * Build a unionAll ledger (invoices + payments) for a given customer + currency.
     * Payments get pay_first = 0 so they win date ties over invoices (pay_first = 1).
     */
    private function makeLedger(int $currencyId): \Illuminate\Database\Query\Builder
    {
        // Excluded invoice IDs that have approved credit notes
        $excludedInvoiceIds = Invoice::whereHas('credit_notes', function ($q) {
            $q->where('authorization', 'approved');
        })->pluck('id')->toArray();

        $inv = DB::table('invoices')
            ->select([
                'date',
                'created_at',
                DB::raw('1 as pay_first'),
                DB::raw('CAST(accrual_balance AS DECIMAL(20,2)) AS accrual_balance'),
            ])
            ->where('authorization', 'approved')
            ->where('customer_id', $this->selectedCustomer)
            ->where('currency_id', $currencyId)
            ->whereNull('deleted_at')
            ->when(!empty($excludedInvoiceIds), function ($q) use ($excludedInvoiceIds) {
                $q->whereNotIn('id', $excludedInvoiceIds);
            });

        $pay = DB::table('payments')
            ->select([
                'date',
                'created_at',
                DB::raw('0 as pay_first'),
                DB::raw('CAST(accrual_balance AS DECIMAL(20,2)) AS accrual_balance'),
            ])
            ->where('customer_id', $this->selectedCustomer)
            ->whereNotNull('accrual_balance')
            ->where('currency_id', $currencyId)
            ->whereNull('deleted_at');

        return $inv->unionAll($pay);
    }

    /**
     * Compute opening & closing accrual balances for every currency
     * using the same snapshot logic as the Index blade.
     */
    private function computeBalances(): void
    {
        $currencies = Currency::all();

        foreach ($currencies as $currency) {
            // Opening: last transaction strictly BEFORE $from
            $this->openingBalances[$currency->id] = DB::query()
                ->fromSub($this->makeLedger($currency->id), 'ledger')
                ->where('date', '<', $this->from)
                ->orderBy('date', 'desc')
                ->orderBy('created_at', 'desc')
                ->orderBy('pay_first', 'asc')   // 0 (payments) wins ties
                ->value('accrual_balance') ?? 0.00;

            // Closing: last transaction on or BEFORE $to
            $this->closingBalances[$currency->id] = DB::query()
                ->fromSub($this->makeLedger($currency->id), 'ledger')
                ->where('date', '<=', $this->to)
                ->orderBy('date', 'desc')
                ->orderBy('created_at', 'desc')
                ->orderBy('pay_first', 'asc')
                ->value('accrual_balance') ?? 0.00;
        }
    }

    public function render()
    {
        if (isset($this->selectedCustomer) && $this->selectedType === 'Outstanding Invoices') {

            $this->invoices = Invoice::where('customer_id', $this->selectedCustomer)
                ->where('authorization', 'approved')
                ->where('status', 'Unpaid')
                ->orWhere('customer_id', $this->selectedCustomer)
                ->where('authorization', 'approved')
                ->where('status', 'Partial')
                ->get();

        } elseif (isset($this->selectedCustomer) && $this->selectedType === 'Account Activity') {

            if (isset($this->from) && isset($this->to)) {

                // Compute opening/closing balances via accrual_balance snapshot
                $this->computeBalances();

                // Invoices leg — note: 'balance' column (not 'total as balance')
                $invoicesQuery = DB::table('invoices')
                    ->select(
                        DB::raw("'invoice' as transaction_type"),
                        'invoice_number as number',
                        'currency_id',
                        'created_at',
                        'date as transaction_date',
                        'total as amount',
                        'balance',           // ← real balance column, not total
                        'accrual_balance'
                    )
                    ->where('authorization', 'approved')
                    ->where('customer_id', $this->selectedCustomer)
                    ->whereNull('deleted_at')
                    ->whereBetween('date', [$this->from, $this->to]);

                // Payments leg union-ed with invoices
                $this->results = DB::table('payments')
                    ->select(
                        DB::raw("'payment' as transaction_type"),
                        'payment_number as number',
                        'currency_id',
                        'created_at',
                        'date as transaction_date',
                        'amount',
                        'balance',
                        'accrual_balance'
                    )
                    ->where('customer_id', $this->selectedCustomer)
                    ->whereNull('deleted_at')
                    ->whereBetween('date', [$this->from, $this->to])
                    ->union($invoicesQuery)
                    ->get();
            }
        }

        return view('livewire.customer-statements.preview', [
            'invoices'        => $this->invoices,
            'results'         => $this->results,
            'openingBalances' => $this->openingBalances,
            'closingBalances' => $this->closingBalances,
        ]);
    }
}
