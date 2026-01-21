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

    public $cost_of_goods_sold_accounts;
    public $operating_expenses_accounts;

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

    /**
     * Flat line items:
     * each item:
     * [
     *   'date' => '2026-01-01',
     *   'bill_number' => 'BILL0001',
     *   'trip_ref' => 'TRIP0001',
     *   'account_name' => 'Fuel',
     *   'item_name' => 'Shell Diesel',
     *   'expense_currency' => 'USD ($)',
     *   'amount' => 123.45, // in reporting currency
     * ]
     */
    public $cogs_items = [];
    public $opex_items = [];

    // UI toggle
    public $summary = 'summary';
    public $details = null;

    public function mount()
    {
        $this->company = Auth::user()->employee->company;

        $this->to   = Carbon::today()->format('Y-m-d');
        $this->from = Carbon::now()->startOfMonth()->format('Y-m-d');

        $this->default_currency    = $this->company->currency;
        $this->default_currency_id = $this->company->currency_id;

        $this->horses = Horse::orderBy('registration_number', 'asc')->get();

        $cogsType = AccountType::where('name', 'Cost Of Goods Sold')->first();
        $opexType = AccountType::where('name', 'Operating Expense')->first();

        $this->cost_of_goods_sold_accounts = $cogsType?->accounts ?? collect();
        $this->operating_expenses_accounts = $opexType?->accounts ?? collect();

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

    public function updatedSelectedHorse() { $this->recalculate(); }
    public function updatedFrom()         { $this->recalculate(); }
    public function updatedTo()           { $this->recalculate(); }

    public function generateStatement()
    {
        $this->recalculate();
    }

    protected function resetNumbers(): void
    {
        $this->selected_horse = null;

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

        $this->cogs_items = [];
        $this->opex_items = [];
    }

    public function recalculate(): void
    {
        if (empty($this->selectedHorse) || empty($this->from) || empty($this->to)) {
            $this->resetNumbers();
            return;
        }

        $horseId = (int) $this->selectedHorse;
        $this->selected_horse = Horse::find($horseId);

        $this->fromDt = Carbon::parse($this->from)->startOfDay();
        $this->toDt   = Carbon::parse($this->to)->endOfDay();

        // Trips / Fuel stats
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

        // Income in reporting currency
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
            ->sum('exchange_customer_freight'); // assumed already in reporting currency

        $this->total_income = $incomeBase + $incomeFxBaseEquivalent;

        // Line items
        $this->cogs_items = $this->fetchExpenseItemsFlat(
            $this->cost_of_goods_sold_accounts,
            tripOnly: true,
            excludeAllowances: true
        );

        $this->opex_items = $this->fetchExpenseItemsFlat(
            $this->operating_expenses_accounts,
            tripOnly: false,
            excludeAllowances: false
        );

        // Totals from line items
        $this->total_cost_of_goods_sold = array_sum(array_map(fn($x) => (float)($x['amount'] ?? 0), $this->cogs_items));
        $this->total_operating_expenses = array_sum(array_map(fn($x) => (float)($x['amount'] ?? 0), $this->opex_items));

        // Profits
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
     * Flat BillExpense line items, filtered correctly.
     * - COGS: uses trips.start_date (matches revenue)
     * - OPEX: uses bills.bill_date
     */
    protected function fetchExpenseItemsFlat($accounts, bool $tripOnly, bool $excludeAllowances): array
    {
        $horseId    = (int) $this->selectedHorse;
        $accountIds = $accounts->pluck('id')->values()->all();
        if (empty($accountIds)) return [];

        // Join to prevent OR leakage and to allow trip-date filtering cleanly
        $q = BillExpense::query()
            ->select('bill_expenses.*')
            ->whereIn('bill_expenses.account_id', $accountIds)
            ->when($excludeAllowances, fn($x) => $x->whereNull('bill_expenses.allowance_id'))
            ->join('bills', 'bills.id', '=', 'bill_expenses.bill_id')
            ->leftJoin('trips', 'trips.id', '=', 'bills.trip_id')
            ->where('bills.authorization', 'approved')
            ->where(function ($x) use ($horseId) {
                $x->where('bills.horse_id', $horseId)
                  ->orWhere('trips.horse_id', $horseId);
            })
            ->when($tripOnly, function ($x) {
                $x->whereNotNull('bills.trip_id')
                  ->whereBetween('trips.start_date', [$this->fromDt, $this->toDt]);
            }, function ($x) {
                $x->whereBetween('bills.bill_date', [$this->fromDt, $this->toDt]);
            })
            ->with([
                'account:id,name',

                'expense:id,name',
                'product:id,name,brand_id',
                'product.brand:id,name',
                'inventory:id,product_id',
                'inventory.product:id,name,brand_id',
                'inventory.product.brand:id,name',

                'bill:id,bill_number,currency_id,bill_date,trip_id',
                'bill.currency:id,name,symbol',
                'bill.trip:id,trip_number,start_date',
            ])
            ->orderBy('bills.bill_date', 'asc')
            ->orderBy('bill_expenses.id', 'asc');

        $rows = $q->get();

        $items = [];
        foreach ($rows as $be) {
            $bill = $be->bill;

            $billDate = $bill?->bill_date ? Carbon::parse($bill->bill_date)->format('Y-m-d') : null;

            $expenseCurrency = trim(
                ($bill?->currency?->name ?? '') .
                (!empty($bill?->currency?->symbol) ? ' (' . $bill->currency->symbol . ')' : '')
            );

            // Amount in reporting currency
            $amount = ((int)($bill?->currency_id) === (int)$this->default_currency_id)
                ? (float) $be->subtotal_incl
                : (float) $be->exchange_amount;

            // Skip true zeros (optional)
            if (abs($amount) < 0.00001) {
                continue;
            }

            $items[] = [
                'date'            => $billDate,
                'bill_number'     => $bill?->bill_number ?? '',
                'trip_ref'        => $bill?->trip?->trip_number ?? ($bill?->trip_id ? ('Trip #' . $bill->trip_id) : ''),
                'account_name'    => $be->account?->name ?? '—',
                'item_name'       => $this->resolveBillExpenseName($be),
                'expense_currency'=> $expenseCurrency,
                'amount'          => $amount,
            ];
        }

        return $items;
    }

    protected function resolveBillExpenseName($bill_expense): string
    {
        if ($bill_expense->expense) {
            return (string) ($bill_expense->expense->name ?? '—');
        }

        if ($bill_expense->product) {
            $brand = $bill_expense->product->brand?->name ?? '';
            $name  = $bill_expense->product->name ?? '';
            return trim($brand . ' ' . $name) ?: '—';
        }

        if ($bill_expense->inventory && $bill_expense->inventory->product) {
            $brand = $bill_expense->inventory->product->brand?->name ?? '';
            $name  = $bill_expense->inventory->product->name ?? '';
            return trim($brand . ' ' . $name) ?: '—';
        }

        return '—';
    }

    public function render()
    {
        return view('livewire.horses.profit-loss.index');
    }
}