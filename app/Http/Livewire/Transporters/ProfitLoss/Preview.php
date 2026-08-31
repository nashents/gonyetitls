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
use App\Models\BillExpense;

class Preview extends Component
{
    public $selectedTransporter;
    public $selected_transporter;

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

    public function mount($selectedTransporter, $from, $to)
    {
        $this->company = Auth::user()->employee->company;

        $this->selectedTransporter  = (int) $selectedTransporter;
        $this->selected_transporter = Transporter::findOrFail($this->selectedTransporter);

        $this->from = $from;
        $this->to   = $to;

        $this->fromDt = Carbon::parse($from)->startOfDay();
        $this->toDt   = Carbon::parse($to)->endOfDay();

        $this->default_currency    = $this->company->currency;
        $this->default_currency_id = $this->company->currency_id;

        $cogsType = AccountType::where('name', 'Cost Of Goods Sold')->first();
        $opexType = AccountType::where('name', 'Operating Expense')->first();

        $this->cost_of_goods_sold_accounts = $cogsType?->accounts ?? collect();
        $this->operating_expenses_accounts = $opexType?->accounts ?? collect();

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
            ->get([
                'id', 'currency_id',
                'freight', 'exchange_customer_freight',
                'transporter_agreement', 'transporter_freight', 'exchange_transporter_freight',
            ]);

        $this->total_trips = $matchingTrips->count();

        $totalIncome = 0.0;
        foreach ($matchingTrips as $trip) {
            $sameCurrency = ((int) $trip->currency_id === (int) $this->default_currency_id);

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

                'bill:id,bill_number,currency_id,bill_date,trip_id,horse_id,trailer_id,driver_id',
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

            if (abs($amount) < 0.00001) continue;

            [$resourceType, $resourceName] = $this->resolveResource($bill);

            $items[] = [
                'date'            => $billDate,
                'bill_number'     => $bill?->bill_number ?? '',
                'trip_ref'        => $bill?->trip?->trip_number ?? ($bill?->trip_id ? ('Trip #'.$bill->trip_id) : ''),
                'account_name'    => $be->account?->name ?? '—',
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
            $name = trim($bill->horse->registration_number . ($bill->horse->fleet_number ? ' (' . $bill->horse->fleet_number . ')' : ''));
            return ['Truck', $name ?: ('Truck #' . $bill->horse_id)];
        }

        if ($bill->trailer_id && $bill->trailer) {
            $name = trim($bill->trailer->registration_number . ($bill->trailer->fleet_number ? ' (' . $bill->trailer->fleet_number . ')' : ''));
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
