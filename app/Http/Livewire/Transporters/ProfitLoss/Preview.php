<?php

namespace App\Http\Livewire\Transporters\ProfitLoss;

use Carbon\Carbon;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

use App\Models\Trip;
use App\Models\Fuel;
use App\Models\Horse;
use App\Models\Driver;
use App\Models\Trailer;
use App\Models\Transporter;
use App\Models\AccountType;
use App\Models\AccountTypeGroup;
use App\Models\BillExpense;

class Preview extends Component
{
    public $selectedTransporter;
    public $selected_transporter;

    public $from;
    public $to;
    public $fromDt;
    public $toDt;

    // 'accrual' (default) or 'cash' — see Index.php for the full explanation.
    public $basis = 'accrual';

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

    public $total_trucks = 0;
    public $total_trips = 0;
    public $total_fuel_orders = 0;
    public $total_fuel = 0;

    // Expenses grouped by the resource that incurred them
    public $total_truck_expenses = 0;
    public $total_trailer_expenses = 0;
    public $total_driver_expenses = 0;
    public $total_other_expenses = 0;

    protected $fleetHorseIds = [];
    protected $fleetTrailerIds = [];
    protected $fleetDriverIds = [];
    protected $isThirdPartyTransporter = false;

    // Flat line items (same format as Index)
    public $cogs_items = [];
    public $opex_items = [];

    // Summary by account for display in preview (Account => total)
    public $cogs_lines = [];
    public $opex_lines = [];

    // opex_items grouped by the bill expense's account type — Operating Expense,
    // Payment Processing Fee, Payroll Expense, Uncategorized Expense, Loss On
    // Foreign Exchange, and any other account type under the Expenses group.
    public $opex_groups = [];

    public function mount($selectedTransporter, $from, $to, $basis = 'accrual')
    {
        $this->company = Auth::user()->employee->company;

        $this->selectedTransporter  = (int) $selectedTransporter;
        $this->selected_transporter = Transporter::findOrFail($this->selectedTransporter);

        $this->from = $from;
        $this->to   = $to;
        $this->basis = in_array($basis, ['cash', 'accrual']) ? $basis : 'accrual';

        $this->fromDt = Carbon::parse($from)->startOfDay();
        $this->toDt   = Carbon::parse($to)->endOfDay();

        $this->default_currency    = $this->company->currency;
        $this->default_currency_id = $this->company->currency_id;

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

        $this->recalculate();
    }

    public function recalculate(): void
    {
        $transporterId = (int) $this->selectedTransporter;

        $this->fleetHorseIds   = Horse::query()->where('transporter_id', $transporterId)->pluck('id')->all();
        $this->fleetTrailerIds = Trailer::query()->where('transporter_id', $transporterId)->pluck('id')->all();
        $this->fleetDriverIds  = Driver::query()->where('transporter_id', $transporterId)->pluck('id')->all();

        $this->total_trucks = count($this->fleetHorseIds);
        $this->isThirdPartyTransporter = !((bool) ($this->selected_transporter->default ?? false));

        $this->total_fuel_orders = Fuel::query()
            ->whereIn('horse_id', $this->fleetHorseIds)
            ->where('authorization', 'approved')
            ->whereBetween('date', [$this->fromDt, $this->toDt])
            ->count();

        $this->total_fuel = (float) Fuel::query()
            ->whereIn('horse_id', $this->fleetHorseIds)
            ->where('authorization', 'approved')
            ->whereBetween('date', [$this->fromDt, $this->toDt])
            ->sum('quantity');

        $matchingTrips = Trip::query()
            ->where(function ($x) use ($transporterId) {
                $x->where('transporter_id', $transporterId);
                if (!empty($this->fleetHorseIds)) {
                    $x->orWhereIn('horse_id', $this->fleetHorseIds);
                }
            })
            ->where('authorization', 'approved')
            ->where('trip_status', '!=', 'Cancelled')
            ->whereBetween('start_date', [$this->fromDt, $this->toDt])
            ->when($this->basis === 'cash', fn ($x) => $x->whereNotNull('paid_at'))
            ->get([
                'id', 'currency_id',
                'freight', 'exchange_customer_freight',
                'transporter_agreement', 'transporter_freight', 'exchange_transporter_freight',
                'amount_paid', 'exchange_amount_paid',
            ]);

        $this->total_trips = $matchingTrips->count();

        $totalIncome = 0.0;
        foreach ($matchingTrips as $trip) {
            $sameCurrency = ((int) $trip->currency_id === (int) $this->default_currency_id);

            if ($this->basis === 'cash') {
                $totalIncome += $sameCurrency
                    ? (float) $trip->amount_paid
                    : (float) ($trip->exchange_amount_paid ?? $trip->amount_paid);
                continue;
            }

            $useTransporterFreight = $this->isThirdPartyTransporter
                && (bool) $trip->transporter_agreement
                && (float) $trip->transporter_freight > 0;

            if ($useTransporterFreight) {
                $totalIncome += $sameCurrency
                    ? (float) $trip->transporter_freight
                    : (float) $trip->exchange_transporter_freight;
            } else {
                $totalIncome += $sameCurrency
                    ? (float) $trip->freight
                    : (float) $trip->exchange_customer_freight;
            }
        }

        $this->total_income = $totalIncome;

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

        $this->total_cost_of_goods_sold = array_sum(array_map(fn($x) => (float)($x['amount'] ?? 0), $this->cogs_items));
        $this->total_operating_expenses = array_sum(array_map(fn($x) => (float)($x['amount'] ?? 0), $this->opex_items));

        $this->cogs_lines = $this->groupItemsByAccount($this->cogs_items);
        $this->opex_lines = $this->groupItemsByAccount($this->opex_items);
        $this->opex_groups = $this->groupByAccountType($this->opex_items);

        $allItems = array_merge($this->cogs_items, $this->opex_items);
        $this->total_truck_expenses   = $this->sumByResource($allItems, 'Truck');
        $this->total_trailer_expenses = $this->sumByResource($allItems, 'Trailer');
        $this->total_driver_expenses  = $this->sumByResource($allItems, 'Driver');
        $this->total_other_expenses   = $this->sumByResource($allItems, 'Other');

        $this->gross_profit = $this->total_income - $this->total_cost_of_goods_sold;
        $this->gross_profit_percentage = ($this->total_income != 0)
            ? ($this->gross_profit / $this->total_income) * 100
            : 0;

        $this->net_profit = $this->gross_profit - $this->total_operating_expenses;
        $this->net_profit_percentage = ($this->total_income != 0)
            ? ($this->net_profit / $this->total_income) * 100
            : 0;
    }

    protected function sumByResource(array $items, string $resourceType): float
    {
        return array_sum(array_map(
            fn($x) => (float) ($x['amount'] ?? 0),
            array_filter($items, fn($x) => ($x['resource_type'] ?? 'Other') === $resourceType)
        ));
    }

    protected function groupItemsByAccount(array $items): array
    {
        $map = [];
        foreach ($items as $it) {
            $key = $it['account_name'] ?? '—';
            $map[$key] = ($map[$key] ?? 0) + (float)($it['amount'] ?? 0);
        }

        $lines = [];
        foreach ($map as $name => $amt) {
            if (abs((float)$amt) < 0.00001) continue;
            $lines[] = ['name' => $name, 'amount' => (float)$amt];
        }

        usort($lines, fn($a, $b) => strcmp($a['name'], $b['name']));
        return $lines;
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
        $transporterId = (int) $this->selectedTransporter;
        $accountIds = $accounts->pluck('id')->values()->all();
        if (empty($accountIds)) return [];

        $q = BillExpense::query()
            ->select('bill_expenses.*')
            ->whereIn('bill_expenses.account_id', $accountIds)
            ->when($excludeAllowances, fn($x) => $x->whereNull('bill_expenses.allowance_id'))
            ->join('bills', 'bills.id', '=', 'bill_expenses.bill_id')
            ->leftJoin('trips', 'trips.id', '=', 'bills.trip_id')
            ->where('bills.authorization', 'approved')
            ->where(function ($x) use ($transporterId) {
                $x->where('bills.transporter_id', $transporterId)
                  ->orWhere('trips.transporter_id', $transporterId);

                if (!empty($this->fleetHorseIds)) {
                    $x->orWhereIn('bills.horse_id', $this->fleetHorseIds)
                      ->orWhereIn('trips.horse_id', $this->fleetHorseIds);
                }
                if (!empty($this->fleetTrailerIds)) {
                    $x->orWhereIn('bills.trailer_id', $this->fleetTrailerIds);
                }
                if (!empty($this->fleetDriverIds)) {
                    $x->orWhereIn('bills.driver_id', $this->fleetDriverIds);
                }
            })
            ->when($this->isThirdPartyTransporter, function ($x) {
                $x->where(function ($y) {
                    $y->whereNull('bills.category')
                      ->orWhere('bills.category', '!=', 'Trip Expense - Transporter Payment');
                });
            })
            ->when($tripOnly, function ($x) {
                // COGS bills tied to a trip (fuel, tolls, etc.) date-match against
                // that trip's start_date to align with the revenue it earned. But a
                // COGS-categorized bill entered directly against the transporter —
                // e.g. driver salaries — never gets a trip_id at all (Bills\Create
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
            ->when($this->basis === 'cash', fn ($x) => $x->whereIn('bills.status', ['Paid', 'Partial']))
            ->with([
                'account:id,name,account_type_id',
                'account.account_type:id,name',

                'expense:id,name',
                'product:id,name,brand_id',
                'product.brand:id,name',
                'inventory:id,product_id',
                'inventory.product:id,name,brand_id',
                'inventory.product.brand:id,name',

                'bill:id,bill_number,currency_id,bill_date,trip_id,horse_id,trailer_id,driver_id,total,balance,status',
                'bill.currency:id,name,symbol',
                'bill.trip:id,trip_number,start_date',
                'bill.horse:id,registration_number,fleet_number',
                'bill.trailer:id,registration_number,fleet_number',
                'bill.driver:id,employee_id',
                'bill.driver.employee:id,name,surname',
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

            $amount = ((int)($bill?->currency_id) === (int)$this->default_currency_id)
                ? (float) $be->subtotal_incl
                : (float) $be->exchange_amount;

            if ($this->basis === 'cash') {
                $billTotal = (float) ($bill?->total ?? 0);
                $billBalance = (float) ($bill?->balance ?? 0);
                $paidFraction = $billTotal > 0 ? max(0, min(1, ($billTotal - $billBalance) / $billTotal)) : 0;
                $amount *= $paidFraction;
            }

            if (abs($amount) < 0.00001) continue;

            [$resourceType, $resourceName] = $this->resolveResource($bill);

            $items[] = [
                'date'            => $billDate,
                'bill_number'     => $bill?->bill_number ?? '',
                'trip_ref'        => $bill?->trip?->trip_number ?? ($bill?->trip_id ? ('Trip #'.$bill->trip_id) : ''),
                'account_name'    => $be->account?->name ?? '—',
                'account_type_name' => $be->account?->account_type?->name ?? 'Uncategorized',
                'item_name'       => $this->resolveBillExpenseName($be),
                'resource_type'   => $resourceType,
                'resource_name'   => $resourceName,
                'expense_currency'=> $expenseCurrency,
                'amount'          => $amount,
            ];
        }

        return $items;
    }

    protected function resolveResource($bill): array
    {
        if (!$bill) {
            return ['Other', ''];
        }

        if ($bill->horse_id && $bill->horse) {
            $name = $bill->horse->identifier_label;
            return ['Truck', $name ?: ('Truck #' . $bill->horse_id)];
        }

        if ($bill->trailer_id && $bill->trailer) {
            $name = $bill->trailer->identifier_label;
            return ['Trailer', $name ?: ('Trailer #' . $bill->trailer_id)];
        }

        if ($bill->driver_id && $bill->driver) {
            $name = trim(($bill->driver->employee->name ?? '') . ' ' . ($bill->driver->employee->surname ?? ''));
            return ['Driver', $name ?: ('Driver #' . $bill->driver_id)];
        }

        return ['Other', ''];
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
        return view('livewire.transporters.profit-loss.preview');
    }
}
