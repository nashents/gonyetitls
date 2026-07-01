<?php

namespace App\Http\Livewire\Reports\Cashflows;

use Carbon\Carbon;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Services\ReportFormatter;
use App\Services\CashFlowCalculator;

class Index extends Component
{
    public $from;
    public $to;
    public $details;
    public $summary = "summary";

    public $default_currency;
    public $default_currency_id;

    public $cash_received_from_customers = 0;
    public $cash_paid_to_vendors = 0;
    public $other_movements = 0;
    public $net_operating_cash_flow = 0;
    public $net_increase_in_cash = 0;
    public $beginning_cash_balance = 0;
    public $ending_cash_balance = 0;

    public function mount()
    {
        $this->to = Carbon::today()->format('Y-m-d');
        $this->from = Carbon::now()->firstOfYear()->format('Y-m-d');
    }

    public function set_report($value)
    {
        $this->summary = null;
        $this->details = null;

        if ($value == "details") {
            $this->details = 'details';
        } elseif ($value == "summary") {
            $this->summary = 'summary';
        }
    }

    /**
     * Bound to the filter form's wire:submit so pressing Enter doesn't
     * error out looking for a missing action; the bound properties
     * already trigger a recalculation on change.
     */
    public function generateStatement()
    {
        //
    }

    public function viewMode(): string
    {
        return $this->details ? 'details' : 'summary';
    }

    public function fmt($value): string
    {
        return ReportFormatter::money($value);
    }

    public function render()
    {
        $user = Auth::user();
        $company = $user->employee?->company ?? $user->company ?? null;
        $this->default_currency = $company?->currency;
        $this->default_currency_id = $company?->currency_id;

        if (isset($this->from) && isset($this->to) && $company) {
            $calculator = new CashFlowCalculator($company->id, $this->from, $this->to);

            $activities = $calculator->operatingActivities();
            $this->cash_received_from_customers = $activities['receipts'];
            $this->cash_paid_to_vendors = $activities['payments'];
            $this->other_movements = $activities['other'];
            $this->net_operating_cash_flow = $activities['receipts'] + $activities['payments'];
            $this->net_increase_in_cash = $this->net_operating_cash_flow + $this->other_movements;

            $this->beginning_cash_balance = $calculator->cashBalanceAsOf($calculator->beginningDate());
            $this->ending_cash_balance = $calculator->cashBalanceAsOf($this->to);
        }

        return view('livewire.reports.cashflows.index');
    }
}
