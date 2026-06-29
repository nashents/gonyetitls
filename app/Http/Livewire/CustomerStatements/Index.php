<?php

namespace App\Http\Livewire\CustomerStatements;

use App\Models\Invoice;
use Livewire\Component;
use App\Models\Customer;
use App\Models\Currency;
use Maatwebsite\Excel\Excel;
use App\Exports\CustomerStatementExport;
use App\Services\CustomerLedgerService;

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
     * Compute opening & closing accrual balances for every currency for the
     * selected customer within the selected date range. The balance is
     * derived live from the transaction ledger (invoices, payments, credit
     * notes) — never read from a stored snapshot column.
     */
    private function computeBalances(): void
    {
        $this->openingBalances = [];
        $this->closingBalances = [];

        if (!$this->selectedCustomer || !$this->from || !$this->to) {
            return;
        }

        $service = app(CustomerLedgerService::class);
        $currencies = Currency::all();

        foreach ($currencies as $currency) {
            $this->openingBalances[$currency->id] = $service->openingBalance(
                (int) $this->selectedCustomer,
                $currency->id,
                $this->from
            );

            $this->closingBalances[$currency->id] = $service->closingBalance(
                (int) $this->selectedCustomer,
                $currency->id,
                $this->to
            );
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

                // Compute opening/closing balances
                $this->computeBalances();

                $service = app(CustomerLedgerService::class);

                $this->results = Currency::all()
                    ->flatMap(fn ($currency) => $service->activity(
                        (int) $this->selectedCustomer,
                        $currency->id,
                        $this->from,
                        $this->to
                    ))
                    ->values();

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
