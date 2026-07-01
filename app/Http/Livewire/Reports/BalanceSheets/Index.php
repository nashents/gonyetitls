<?php

namespace App\Http\Livewire\Reports\BalanceSheets;

use Carbon\Carbon;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Services\ReportFormatter;
use App\Services\BalanceSheetCalculator;

class Index extends Component
{
    const LIABILITIES_GROUP = 'Liabilities & Credit Cards';

    public $as_of_date;
    public $details;
    public $summary = "summary";

    public $default_currency;
    public $default_currency_id;

    public $assets_items = [];
    public $total_assets = 0;

    public $liabilities_items = [];
    public $total_liabilities = 0;

    public $equity_items = [];
    public $net_income = 0;
    public $total_equity = 0;

    public $total_liabilities_and_equity = 0;
    public $is_balanced = false;

    public function mount()
    {
        $this->as_of_date = Carbon::today()->format('Y-m-d');
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

        if (isset($this->as_of_date) && $company) {
            $calculator = new BalanceSheetCalculator($company->id, $this->as_of_date);

            [$this->total_assets, $this->assets_items] = $calculator->groupBalances('Assets');
            [$this->total_liabilities, $this->liabilities_items] = $calculator->groupBalances(self::LIABILITIES_GROUP, creditNormal: true);

            [$equityTotal, $equityItems] = $calculator->groupBalances('Equity', creditNormal: true);
            $this->net_income = $calculator->netIncomeToDate();
            $this->equity_items = array_merge($equityItems, [['label' => 'Net Income', 'amount' => $this->net_income]]);
            $this->total_equity = $equityTotal + $this->net_income;

            $this->total_liabilities_and_equity = $this->total_liabilities + $this->total_equity;
            $this->is_balanced = abs($this->total_assets - $this->total_liabilities_and_equity) < 0.01;
        }

        return view('livewire.reports.balance-sheets.index');
    }
}
