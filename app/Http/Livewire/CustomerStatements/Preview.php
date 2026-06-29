<?php

namespace App\Http\Livewire\CustomerStatements;

use App\Models\Invoice;

use App\Models\Currency;
use Livewire\Component;
use App\Models\Customer;
use Illuminate\Support\Facades\Auth;
use App\Services\CustomerLedgerService;

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
     * Compute opening & closing accrual balances for every currency. The
     * balance is derived live from the transaction ledger (invoices,
     * payments, approved credit notes) — never read from a stored
     * snapshot column.
     */
    private function computeBalances(): void
    {
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
