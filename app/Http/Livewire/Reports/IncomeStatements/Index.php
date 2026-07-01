<?php

namespace App\Http\Livewire\Reports\IncomeStatements;

use Carbon\Carbon;
use Livewire\Component;
use App\Models\AccountType;
use Illuminate\Support\Facades\Auth;
use App\Services\IncomeStatementCalculator;

class Index extends Component
{
    const ACCRUAL = 'Accrual (Paid & Unpaid)';
    const CASH_BASIS = 'Cash Basis';

    public $from;
    public $to;
    public $selectedType = self::ACCRUAL;
    public $details;
    public $summary = "summary";

    public $income_account_type;
    public $income_accounts;

    public $cost_of_goods_sold_account_type;
    public $cost_of_goods_sold_accounts;

    public $operating_expenses_account_type;
    public $operating_expenses_accounts;

    // Per-account breakdowns: [account_id => amount] for the "details" view.
    public $income_by_account = [];
    public $cost_of_goods_sold_by_account = [];
    public $operating_expenses_by_account = [];

    public $default_currency;
    public $default_currency_id;

    public $total_income = 0;
    public $total_cost_of_goods_sold = 0;
    public $total_operating_expenses = 0;

    public $gross_profit = 0;
    public $gross_profit_percentage = 0;
    public $net_profit = 0;
    public $net_profit_percentage = 0;

    public function mount()
    {
        $this->to = Carbon::today()->format('Y-m-d');
        $this->from = Carbon::now()->firstOfYear()->format('Y-m-d');

        $this->income_account_type = AccountType::where('name', 'Income')->first();
        $this->income_accounts = $this->income_account_type?->accounts ?? collect();

        $this->cost_of_goods_sold_account_type = AccountType::where('name', 'Cost Of Goods Sold')->first();
        $this->cost_of_goods_sold_accounts = $this->cost_of_goods_sold_account_type?->accounts ?? collect();

        $this->operating_expenses_account_type = AccountType::where('name', 'Operating Expense')->first();
        $this->operating_expenses_accounts = $this->operating_expenses_account_type?->accounts ?? collect();
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

    /**
     * Whichever toggle ("Summary" / "Details") is currently active, used to
     * keep the Print/Export PDF actions in sync with what's on screen.
     */
    public function viewMode(): string
    {
        return $this->details ? 'details' : 'summary';
    }

    public function fmt($value): string
    {
        return IncomeStatementCalculator::money($value);
    }

    public function pct($value): string
    {
        return IncomeStatementCalculator::percent($value);
    }

    public function render()
    {
        $user = Auth::user();
        $company = $user->employee?->company ?? $user->company ?? null;
        $this->default_currency = $company?->currency;
        $this->default_currency_id = $company?->currency_id;

        if (isset($this->from) && isset($this->to)) {
            $calculator = new IncomeStatementCalculator(
                $this->from,
                $this->to,
                $this->selectedType === self::CASH_BASIS,
                $this->default_currency_id
            );

            [$this->total_income, $this->income_by_account] = $calculator->incomeAmounts();

            $cogsAccountIds = $this->cost_of_goods_sold_accounts->pluck('id')->all();
            [$this->total_cost_of_goods_sold, $this->cost_of_goods_sold_by_account] = $calculator->expenseAmounts($cogsAccountIds);

            $opexAccountIds = $this->operating_expenses_accounts->pluck('id')->all();
            [$this->total_operating_expenses, $this->operating_expenses_by_account] = $calculator->expenseAmounts($opexAccountIds);
        }

        $this->gross_profit = $this->total_income - $this->total_cost_of_goods_sold;
        $this->gross_profit_percentage = $this->total_income != 0
            ? round(($this->gross_profit / $this->total_income) * 100, 2)
            : 0.0;

        $this->net_profit = $this->gross_profit - $this->total_operating_expenses;
        $this->net_profit_percentage = $this->total_income != 0
            ? round(($this->net_profit / $this->total_income) * 100, 2)
            : 0.0;

        return view('livewire.reports.income-statements.index');
    }
}
