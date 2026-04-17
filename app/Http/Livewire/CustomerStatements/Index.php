<?php

namespace App\Http\Livewire\CustomerStatements;

use App\Models\Invoice;
use Livewire\Component;
use App\Models\Customer;
use App\Models\Currency;
use Maatwebsite\Excel\Excel;
use Illuminate\Support\Facades\DB;
use App\Exports\CustomerStatementExport;

class Index extends Component
{
    public $from;
    public $to;
    public $payments;
    public $payment_id;
    public $results;
    protected $invoices;
    public $invoice_id;
    public $customers;
    public $customer;
    public $customer_id;
    public $selectedCustomer;
    public $selectedType;
    public $openingBalances = [];
    public $closingBalances = [];

    public function mount()
    {
        $this->customers = Customer::orderBy('name', 'asc')->where('status', true)->get();
    }

    public function exportCustomerStatementExcel(Excel $excel)
    {
        return $excel->download(
            new CustomerStatementExport($this->selectedType, $this->selectedCustomer, $this->from, $this->to),
            'customer_statement.xlsx'
        );
    }

    /**
     * Build a pure Query Builder union ledger for a given customer + currency.
     * Payments get pay_first = 0 so they win date ties over invoices (pay_first = 1).
     */
    private function makeLedger(int $customerId, int $currencyId): \Illuminate\Database\Query\Builder
    {
        $inv = DB::table('invoices')
            ->select([
                'date',
                'created_at',
                DB::raw('1 as pay_first'),
                DB::raw('CAST(accrual_balance AS DECIMAL(20,2)) AS accrual_balance'),
            ])
            ->where('authorization', 'approved')
            ->where('customer_id', $customerId)
            ->where('currency_id', $currencyId)
            ->whereNotNull('accrual_balance')
            ->whereNull('deleted_at');

        $pay = DB::table('payments')
            ->select([
                'date',
                'created_at',
                DB::raw('0 as pay_first'),
                DB::raw('CAST(accrual_balance AS DECIMAL(20,2)) AS accrual_balance'),
            ])
            ->where('customer_id', $customerId)
            ->whereNotNull('accrual_balance')
            ->where('currency_id', $currencyId)
            ->whereNull('deleted_at');

        return $inv->unionAll($pay);
    }

    /**
     * Compute opening & closing accrual_balance snapshots for every currency
     * for the selected customer within the selected date range.
     */
    private function computeBalances(): void
    {
        $this->openingBalances = [];
        $this->closingBalances = [];

        if (!$this->selectedCustomer || !$this->from || !$this->to) {
            return;
        }

        $currencies = Currency::all();

        foreach ($currencies as $currency) {
            $ledger = $this->makeLedger((int) $this->selectedCustomer, $currency->id);

            // Opening: last transaction strictly BEFORE $from
            $this->openingBalances[$currency->id] = DB::query()
                ->fromSub($ledger, 'ledger')
                ->where('date', '<', $this->from)
                ->orderBy('date', 'desc')
                ->orderBy('created_at', 'desc')
                ->orderBy('pay_first', 'asc')   // 0 (payments) wins ties
                ->value('accrual_balance') ?? 0.00;

            // Closing: last transaction on or BEFORE $to
            $this->closingBalances[$currency->id] = DB::query()
                ->fromSub($this->makeLedger((int) $this->selectedCustomer, $currency->id), 'ledger')
                ->where('date', '<=', $this->to)
                ->orderBy('date', 'desc')
                ->orderBy('created_at', 'desc')
                ->orderBy('pay_first', 'asc')
                ->value('accrual_balance') ?? 0.00;
        }
    }

    public function updatedSelectedCustomer($id)
    {
        if (!is_null($id)) {
            $this->selectedCustomer = $id;
            $this->customer = Customer::find($this->selectedCustomer);
            $this->loadInvoices();
        }
    }

    public function updatedSelectedType($type)
    {
        if (!is_null($type)) {
            $this->selectedType = $type;
            $this->loadInvoices();
        }
    }

    public function updatedFrom()
    {
        $this->loadInvoices();
    }

    public function updatedTo()
    {
        $this->loadInvoices();
    }

    public function generateStatement()
    {
        $this->loadInvoices();
    }

    private function loadInvoices(): void
    {
        if (!isset($this->selectedCustomer) || !$this->selectedCustomer) {
            return;
        }

        if ($this->selectedType === 'Outstanding Invoices') {

            $this->invoices = Invoice::where('customer_id', $this->selectedCustomer)
                ->where('authorization', 'approved')
                ->where(function ($q) {
                    $q->where('status', 'Unpaid')
                      ->orWhere('status', 'Partial');
                })
                ->get();

            // Reset account activity state
            $this->results         = null;
            $this->openingBalances = [];
            $this->closingBalances = [];

        } elseif ($this->selectedType === 'Account Activity') {

            if (isset($this->from) && isset($this->to)) {

                // Compute opening/closing snapshots
                $this->computeBalances();

                // Invoices leg
                $invoicesQuery = DB::table('invoices')
                    ->select(
                        'invoice_number as number',
                        'currency_id',
                        'date as transaction_date',
                        'total as amount',
                        'balance',
                        'accrual_balance',
                        'created_at'
                    )
                    ->where('authorization', 'approved')
                    ->where('customer_id', $this->selectedCustomer)
                    ->whereNull('deleted_at')
                    ->whereBetween('date', [$this->from, $this->to]);

                // Payments leg
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
                    ->where('customer_id', $this->selectedCustomer)
                    ->whereNull('deleted_at')
                    ->whereBetween('date', [$this->from, $this->to])
                    ->union($invoicesQuery)
                    ->get()
                    ->sortBy([
                        ['transaction_date', 'asc'],
                        ['accrual_balance', 'asc'],
                    ]);

                $this->invoices = $this->results; // used for ->count() check in blade
            }
        }
    }

    public function customerStatementPreview($selectedType = null, $selectedCustomer = null, $from = null, $to = null)
    {
        $this->emit('showCustomerStatement', ['selectedType' => $selectedType]);
    }

    public function render()
    {
        $this->loadInvoices();

        return view('livewire.customer-statements.index', [
            'invoices'        => $this->invoices,
            'openingBalances' => $this->openingBalances,
            'closingBalances' => $this->closingBalances,
        ]);
    }
}
