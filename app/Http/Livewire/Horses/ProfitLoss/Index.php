<?php

namespace App\Http\Livewire\Horses\ProfitLoss;

use Carbon\Carbon;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

use App\Models\Trip;
use App\Models\Fuel;
use App\Models\Horse;
use App\Models\AccountType;
use App\Models\BillExpense;

class Index extends Component
{
    public $horses;

    public $selectedHorse;
    public $selected_horse;

    public $from;
    public $to;
    public $fromDt;
    public $toDt;

    public $company;
    public $default_currency;
    public $default_currency_id;

    public $income_accounts;
    public $cost_of_goods_sold_accounts;
    public $operating_expenses_accounts;

    public $cogs_lines = [];
    public $opex_lines = [];

    public $total_income = 0;
    public $total_cost_of_goods_sold = 0;
    public $total_operating_expenses = 0;

    public $gross_profit = 0;
    public $gross_profit_percentage = 0;

    public $net_profit = 0;
    public $net_profit_percentage = 0;

    public $total_trips = 0;
    public $total_fuel_orders = 0;
    public $total_fuel = 0;

    // toggle state used by your blade
    public $summary = 'summary';
    public $details = null;

    public function mount()
    {
        $this->company = Auth::user()->employee->company;

        // Dates default
        $this->to   = Carbon::today()->format('Y-m-d');
        $this->from = Carbon::now()->startOfMonth()->format('Y-m-d');

        // Currency
        $this->default_currency    = $this->company->currency;
        $this->default_currency_id = $this->company->currency_id;

        // Dropdown horses
        $this->horses = Horse::orderBy('registration_number', 'asc')->get();

        // Accounts
        $incomeType = AccountType::where('name', 'Income')->first();
        $cogsType   = AccountType::where('name', 'Cost Of Goods Sold')->first();
        $opexType   = AccountType::where('name', 'Operating Expense')->first();

        $this->income_accounts = $incomeType?->accounts ?? collect();
        $this->cost_of_goods_sold_accounts = $cogsType?->accounts ?? collect();
        $this->operating_expenses_accounts = $opexType?->accounts ?? collect();

        // Do NOT recalc until horse is selected
        $this->resetNumbers();
    }

    public function set_report($type)
    {
        if ($type === 'summary') {
            $this->summary = 'summary';
            $this->details = null;
        } else {
            $this->details = 'details';
            $this->summary = null;
        }
    }

    // Recalculate whenever filters change
    public function updatedSelectedHorse()
    {
        $this->recalculate();
    }

    public function updatedFrom()
    {
        $this->recalculate();
    }

    public function updatedTo()
    {
        $this->recalculate();
    }

    public function generateStatement()
    {
        // If your blade is still submitting, keep this method
        $this->recalculate();
    }

    protected function resetNumbers(): void
    {
        $this->selected_horse = null;

        $this->cogs_lines = [];
        $this->opex_lines = [];

        $this->total_income = 0;
        $this->total_cost_of_goods_sold = 0;
        $this->total_operating_expenses = 0;

        $this->gross_profit = 0;
        $this->gross_profit_percentage = 0;

        $this->net_profit = 0;
        $this->net_profit_percentage = 0;

        $this->total_trips = 0;
        $this->total_fuel_orders = 0;
        $this->total_fuel = 0;
    }

    public function recalculate(): void
    {
        // Guard: must have horse and dates
        if (empty($this->selectedHorse) || empty($this->from) || empty($this->to)) {
            $this->resetNumbers();
            return;
        }

        $horseId = (int) $this->selectedHorse;

        $this->selected_horse = Horse::find($horseId);

        $this->fromDt = Carbon::parse($this->from)->startOfDay();
        $this->toDt   = Carbon::parse($this->to)->endOfDay();

        // ---------- Counts / usage ----------
        $this->total_trips = Trip::query()
            ->where('horse_id', $horseId)
            ->where('authorization', 'approved')
            ->where('trip_status', '!=', 'Cancelled')
            ->whereBetween('start_date', [$this->fromDt, $this->toDt])
            ->count();

        $this->total_fuel_orders = Fuel::query()
            ->where('horse_id', $horseId)
            ->where('authorization', 'approved')
            ->whereBetween('date', [$this->fromDt, $this->toDt])
            ->count();

        $this->total_fuel = (float) Fuel::query()
            ->where('horse_id', $horseId)
            ->where('authorization', 'approved')
            ->whereBetween('date', [$this->fromDt, $this->toDt])
            ->sum('quantity');

        // ---------- Income (company currency) ----------
        $incomeBase = (float) Trip::query()
            ->where('horse_id', $horseId)
            ->where('authorization', 'approved')
            ->where('trip_status', '!=', 'Cancelled')
            ->whereBetween('start_date', [$this->fromDt, $this->toDt])
            ->where('currency_id', $this->default_currency_id)
            ->sum('freight');

        $incomeFxBaseEquivalent = (float) Trip::query()
            ->where('horse_id', $horseId)
            ->where('authorization', 'approved')
            ->where('trip_status', '!=', 'Cancelled')
            ->whereBetween('start_date', [$this->fromDt, $this->toDt])
            ->where('currency_id', '!=', $this->default_currency_id)
            ->sum('exchange_customer_freight'); // assumed in company currency

        $this->total_income = $incomeBase + $incomeFxBaseEquivalent;

        // ---------- COGS + OPEX ----------
        [$this->cogs_lines, $this->total_cost_of_goods_sold] = $this->buildExpenseLines(
            $this->cost_of_goods_sold_accounts,
            tripOnly: true,
            excludeAllowances: true
        );

        [$this->opex_lines, $this->total_operating_expenses] = $this->buildExpenseLines(
            $this->operating_expenses_accounts,
            tripOnly: false,
            excludeAllowances: false
        );

        // ---------- Profits ----------
        $this->gross_profit = $this->total_income - $this->total_cost_of_goods_sold;
        $this->gross_profit_percentage = ($this->total_income != 0)
            ? ($this->gross_profit / $this->total_income) * 100
            : 0;

        $this->net_profit = $this->gross_profit - $this->total_operating_expenses;
        $this->net_profit_percentage = ($this->total_income != 0)
            ? ($this->net_profit / $this->total_income) * 100
            : 0;
    }

    /**
     * Accurate + matches Trips filter for COGS:
     * - If tripOnly=true: filter by trips.start_date (same basis as revenue)
     * - If tripOnly=false: filter by bills.bill_date
     */
    protected function buildExpenseLines($accounts, bool $tripOnly, bool $excludeAllowances): array
    {
        $horseId    = (int) $this->selectedHorse;
        $accountIds = $accounts->pluck('id')->values()->all();

        if (empty($accountIds)) {
            return [[], 0.0];
        }

        $base = BillExpense::query()
            ->whereIn('bill_expenses.account_id', $accountIds)
            ->when($excludeAllowances, fn($q) => $q->whereNull('bill_expenses.allowance_id'))
            ->join('bills', 'bills.id', '=', 'bill_expenses.bill_id')
            ->leftJoin('trips', 'trips.id', '=', 'bills.trip_id')
            ->where('bills.authorization', 'approved')
            ->where(function ($q) use ($horseId) {
                $q->where('bills.horse_id', $horseId)
                  ->orWhere('trips.horse_id', $horseId);
            })
            ->when($tripOnly, function ($q) {
                $q->whereNotNull('bills.trip_id')
                  ->whereBetween('trips.start_date', [$this->fromDt, $this->toDt]);
            }, function ($q) {
                $q->whereBetween('bills.bill_date', [$this->fromDt, $this->toDt]);
            });

        // Default currency sums use subtotal_incl
        $baseByAccount = (clone $base)
            ->where('bills.currency_id', $this->default_currency_id)
            ->selectRaw('bill_expenses.account_id, SUM(CAST(bill_expenses.subtotal_incl AS DECIMAL(18,2))) as amount')
            ->groupBy('bill_expenses.account_id')
            ->pluck('amount', 'bill_expenses.account_id');

        // Foreign currency sums use exchange_amount (assumed base-currency equivalent)
        $fxByAccount = (clone $base)
            ->where('bills.currency_id', '!=', $this->default_currency_id)
            ->selectRaw('bill_expenses.account_id, SUM(CAST(bill_expenses.exchange_amount AS DECIMAL(18,2))) as amount')
            ->groupBy('bill_expenses.account_id')
            ->pluck('amount', 'bill_expenses.account_id');

        $lines = [];
        $total = 0.0;

        foreach ($accounts as $acc) {
            $amount = (float) ($baseByAccount[$acc->id] ?? 0) + (float) ($fxByAccount[$acc->id] ?? 0);

            if (abs($amount) < 0.00001) {
                continue;
            }

            $lines[] = [
                'name'   => $acc->name,
                'amount' => $amount,
            ];

            $total += $amount;
        }

        return [$lines, $total];
    }

    public function render()
    {
        return view('livewire.horses.profit-loss.index');
    }
}