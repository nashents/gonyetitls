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

class Preview extends Component
{
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

    // Prepared statement lines (no DB work in Blade)
    public $cogs_lines = []; // [ ['name' => 'Fuel', 'amount' => 123.45], ... ]
    public $opex_lines = [];

    // Totals
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

    public function mount($selectedHorse, $from, $to)
    {
        $this->company = Auth::user()->employee->company;

        $this->selectedHorse = (int) $selectedHorse;
        $this->selected_horse = Horse::findOrFail($this->selectedHorse);

        $this->from = $from;
        $this->to = $to;

        $this->fromDt = Carbon::parse($from)->startOfDay();
        $this->toDt   = Carbon::parse($to)->endOfDay();

        $this->default_currency    = $this->company->currency;
        $this->default_currency_id = $this->company->currency_id;

        // Account groups
        $incomeType = AccountType::where('name', 'Income')->first();
        $cogsType   = AccountType::where('name', 'Cost Of Goods Sold')->first();
        $opexType   = AccountType::where('name', 'Operating Expense')->first();

        $this->income_accounts = $incomeType?->accounts ?? collect();
        $this->cost_of_goods_sold_accounts = $cogsType?->accounts ?? collect();
        $this->operating_expenses_accounts = $opexType?->accounts ?? collect();

        $this->recalculate();
    }

    public function recalculate(): void
    {
        $horseId = $this->selectedHorse;

        // ---------- Counts / usage ----------
        $this->total_trips = Trip::query()
            ->where('horse_id', $horseId)
            ->where('authorization', 'approved')
            ->where('trip_status', '!=', 'Cancelled')
            ->whereBetween('start_date', [$this->fromDt, $this->toDt])
            // ->where('company_id', $this->company->id) // enable if you have it
            ->count();

        $this->total_fuel_orders = Fuel::query()
            ->where('horse_id', $horseId)
            ->where('authorization', 'approved')
            ->whereBetween('date', [$this->fromDt, $this->toDt])
            // ->where('company_id', $this->company->id)
            ->count();

        $this->total_fuel = (float) Fuel::query()
            ->where('horse_id', $horseId)
            ->where('authorization', 'approved')
            ->whereBetween('date', [$this->fromDt, $this->toDt])
            // ->where('company_id', $this->company->id)
            ->sum('quantity');

        // ---------- Revenue (company currency) ----------
        $incomeBase = (float) Trip::query()
            ->where('horse_id', $horseId)
            ->where('authorization', 'approved')
            ->where('trip_status', '!=', 'Cancelled')
            ->whereBetween('start_date', [$this->fromDt, $this->toDt])
            ->where('currency_id', $this->default_currency_id)
            // ->where('company_id', $this->company->id)
            ->sum('freight');

        $incomeFxBaseEquivalent = (float) Trip::query()
            ->where('horse_id', $horseId)
            ->where('authorization', 'approved')
            ->where('trip_status', '!=', 'Cancelled')
            ->whereBetween('start_date', [$this->fromDt, $this->toDt])
            ->where('currency_id', '!=', $this->default_currency_id)
            // ->where('company_id', $this->company->id)
            ->sum('exchange_customer_freight'); // assumed in company currency

        $this->total_income = $incomeBase + $incomeFxBaseEquivalent;

        // ---------- COGS + OPEX (lines + totals) ----------
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
     * Returns:
     *  - lines: [ ['name' => 'Account', 'amount' => 123.45], ... ]
     *  - total: float
     */
    protected function buildExpenseLines($accounts, bool $tripOnly, bool $excludeAllowances): array
    {
        $horseId = $this->selectedHorse;
        $accountIds = $accounts->pluck('id')->values()->all();

        if (empty($accountIds)) {
            return [[], 0.0];
        }

        $baseQuery = BillExpense::query()
            ->whereIn('account_id', $accountIds)
            ->when($excludeAllowances, fn($q) => $q->whereNull('allowance_id'))
            ->whereHas('bill', function ($b) use ($tripOnly) {
                $b->whereBetween('bill_date', [$this->fromDt, $this->toDt])
                  ->where('authorization', 'approved')
                  // ->where('company_id', $this->company->id)
                  ->when($tripOnly, fn($bb) => $bb->whereNotNull('trip_id'));
            })
            // ✅ Critical: group the horse scope so OR doesn't leak
            ->where(function ($q) use ($horseId) {
                $q->whereHas('bill', fn($b) => $b->where('horse_id', $horseId));
            });

        // Sum default currency using subtotal_incl
        $baseByAccount = (clone $baseQuery)
            ->whereHas('bill', fn($b) => $b->where('currency_id', $this->default_currency_id))
            ->selectRaw('account_id, SUM(subtotal) as amount')
            ->groupBy('account_id')
            ->pluck('amount', 'account_id');

        // Sum foreign currency using exchange_amount (assumed base equivalent)
        $fxByAccount = (clone $baseQuery)
            ->whereHas('bill', fn($b) => $b->where('currency_id', '!=', $this->default_currency_id))
            ->selectRaw('account_id, SUM(exchange_amount) as amount')
            ->groupBy('account_id')
            ->pluck('amount', 'account_id');

        $lines = [];
        $total = 0.0;

        foreach ($accounts as $acc) {
            $amount = (float) ($baseByAccount[$acc->id] ?? 0) + (float) ($fxByAccount[$acc->id] ?? 0);

            // Hide zero lines (optional)
            if (abs($amount) < 0.00001) {
                continue;
            }

            $lines[] = [
                'name' => $acc->name,
                'amount' => $amount,
            ];
            $total += $amount;
        }

        return [$lines, $total];
    }

    public function render()
    {
        return view('livewire.horses.profit-loss.preview');
    }
}