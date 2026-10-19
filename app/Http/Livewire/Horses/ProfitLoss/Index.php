<?php

namespace App\Http\Livewire\Horses\ProfitLoss;

use Carbon\Carbon;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

use App\Models\Trip;
use App\Models\Fuel;
use App\Models\Horse;
use App\Models\Transporter;
use App\Models\AccountType;
use App\Models\AccountTypeGroup;
use App\Models\BillExpense;

class Index extends Component
{
    use \App\Http\Livewire\Concerns\ProfitLossLineItems;

    // Horse picker filters: owning transporter and a reg#/fleet# search.
    public $transporters;
    public $horseTransporter;
    public $horseSearch;

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
    /** Approved fuel orders with no cost posted (order numbers) - see fuelStats(). */
    public $uncosted_fuel_orders = [];

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

    // opex_items grouped by the bill expense's account type — Operating Expense,
    // Payment Processing Fee, Payroll Expense, Uncategorized Expense, Loss On
    // Foreign Exchange, and any other account type under the Expenses group.
    public $opex_groups = [];

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

        $this->transporters = Transporter::whereHas('horses')->orderBy('name')->get(['id', 'name', 'transporter_number']);

        $cogsType     = AccountType::where('name', 'Cost Of Goods Sold')->first();
        $expensesGroup = AccountTypeGroup::where('name', 'Expenses')->first();

        $this->cost_of_goods_sold_accounts = $cogsType?->accounts ?? collect();

        // Everything else under the Expenses group — Operating Expense, Payment
        // Processing Fee, Payroll Expense, Uncategorized Expense, Loss On Foreign
        // Exchange — not just accounts typed exactly "Operating Expense".
        $this->operating_expenses_accounts = $expensesGroup
            ? $expensesGroup->accounts()
                ->when($cogsType, fn ($q) => $q->where('account_type_id', '!=', $cogsType->id))
                ->get()
            : collect();

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

    // Jump straight to the horse when the search narrows to a single match.
    public function updatedHorseSearch()
    {
        $matches = $this->filteredHorses();
        if (trim((string) $this->horseSearch) !== '' && $matches->count() === 1 && $matches->first()->id != $this->selectedHorse) {
            $this->selectedHorse = $matches->first()->id;
            $this->recalculate();
        }
    }

    protected function filteredHorses()
    {
        $search = trim((string) $this->horseSearch);

        return Horse::with(['horse_make', 'horse_model'])
            ->when($this->horseTransporter, fn ($q) => $q->where('transporter_id', $this->horseTransporter))
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($q) use ($search) {
                    $q->where('registration_number', 'like', '%' . $search . '%')
                      ->orWhere('fleet_number', 'like', '%' . $search . '%');
                });
            })
            ->orderByIdentifier('asc')
            ->get();
    }
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
        $this->uncosted_fuel_orders = [];

        $this->cogs_items = [];
        $this->opex_items = [];
        $this->opex_groups = [];
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

        [$this->total_fuel_orders, $this->total_fuel, $this->uncosted_fuel_orders] = $this->fuelStats((int) $horseId);

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
        // Customer-supplied fuel posts to Fuel - COGS with no Bill - see ProfitLossLineItems.
        $this->cogs_items = array_merge($this->cogs_items, $this->customerFuelItems(
            (int) $this->selectedHorse,
            $this->cost_of_goods_sold_accounts->pluck('id')->all()
        ));

        $this->opex_items = $this->fetchExpenseItemsFlat(
            $this->operating_expenses_accounts,
            tripOnly: false,
            excludeAllowances: false
        );

        // Totals from line items
        $this->total_cost_of_goods_sold = array_sum(array_map(fn($x) => (float)($x['amount'] ?? 0), $this->cogs_items));
        $this->total_operating_expenses = array_sum(array_map(fn($x) => (float)($x['amount'] ?? 0), $this->opex_items));
        $this->opex_groups = $this->groupByAccountType($this->opex_items);

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
     * Groups flat expense line items by account type (Operating Expense, Payment
     * Processing Fee, Payroll Expense, Uncategorized Expense, Loss On Foreign
     * Exchange, etc.) — whatever account types the bills were actually coded to.
     */
    protected function groupByAccountType(array $items): array
    {
        $groups = [];

        foreach ($items as $item) {
            $type = $item['account_type_name'] ?? 'Uncategorized';
            $groups[$type]['type_name'] ??= $type;
            $groups[$type]['items'][] = $item;
            $groups[$type]['total'] = ($groups[$type]['total'] ?? 0) + (float) ($item['amount'] ?? 0);
        }

        ksort($groups);

        return array_values($groups);
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
                // COGS bills tied to a trip (fuel, tolls, etc.) date-match against
                // that trip's start_date to align with the revenue it earned. But a
                // COGS-categorized bill entered directly against the horse — e.g.
                // driver salaries — never gets a trip_id at all (Bills\Create
                // doesn't set one), so it needs its own bill_date to fall back on
                // instead of being silently excluded from COGS altogether.
                $x->where(function ($y) {
                    $y->where(function ($z) {
                        $z->whereNotNull('bills.trip_id')
                          ->whereBetween('trips.start_date', [$this->fromDt, $this->toDt]);
                    })->orWhere(function ($z) {
                        $z->whereNull('bills.trip_id')
                          ->whereBetween('bills.bill_date', [$this->fromDt, $this->toDt]);
                    });
                });
            }, function ($x) {
                $x->whereBetween('bills.bill_date', [$this->fromDt, $this->toDt]);
            })
            ->with([
                'account:id,name,account_type_id',
                'account.account_type:id,name',

                'expense:id,name',
                'product:id,name,brand_id',
                'product.brand:id,name',
                'inventory:id,product_id',
                'inventory.product:id,name,brand_id',
                'inventory.product.brand:id,name',

                'bill:id,bill_number,currency_id,bill_date,trip_id,exchange_rate',
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
            $amount = $this->reportingAmount($be, $bill);

            // Skip true zeros (optional)
            if (abs($amount) < 0.00001) {
                continue;
            }

            $items[] = [
                'date'            => $billDate,
                'bill_number'     => $bill?->bill_number ?? '',
                'trip_ref'        => $bill?->trip?->trip_number ?? ($bill?->trip_id ? ('Trip #' . $bill->trip_id) : ''),
                'account_name'    => $be->account?->name ?? '—',
                'account_type_name' => $be->account?->account_type?->name ?? 'Uncategorized',
                'item_name'       => $this->resolveBillExpenseName($be),
                'expense_currency'=> $this->currencyLabel($bill?->currency, (float) ($be->subtotal_incl ?: $be->subtotal)),
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
        $horses = $this->filteredHorses();

        // Keep the selected horse in the dropdown even when the filters exclude it.
        if ($this->selectedHorse && ! $horses->contains('id', (int) $this->selectedHorse)) {
            $selected = Horse::find($this->selectedHorse);
            if ($selected) {
                $horses->prepend($selected);
            }
        }

        return view('livewire.horses.profit-loss.index', ['horses' => $horses]);
    }
}