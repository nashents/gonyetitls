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

class Index extends Component
{
    public $transporters;

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

    // IDs of this transporter's own fleet (horses.transporter_id / trailers.transporter_id /
    // drivers.transporter_id), so bills/trips that only reference the resource — without the
    // transporter_id column itself being stamped — are still picked up.
    protected $fleetHorseIds = [];
    protected $fleetTrailerIds = [];
    protected $fleetDriverIds = [];

    // False for the company's own in-house fleet transporter (Transporter::default = true),
    // true for real (subcontracted) transporters.
    protected $isThirdPartyTransporter = false;

    /**
     * Flat line items:
     * each item:
     * [
     *   'date' => '2026-01-01',
     *   'bill_number' => 'BILL0001',
     *   'trip_ref' => 'TRIP0001',
     *   'account_name' => 'Fuel',
     *   'item_name' => 'Shell Diesel',
     *   'resource_type' => 'Truck'|'Trailer'|'Driver'|'Other',
     *   'resource_name' => 'ABC1234GP (12)',
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

        $this->transporters = Transporter::orderBy('name', 'asc')->get();

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

    public function updatedSelectedTransporter() { $this->recalculate(); }
    public function updatedFrom()                { $this->recalculate(); }
    public function updatedTo()                  { $this->recalculate(); }

    public function generateStatement()
    {
        $this->recalculate();
    }

    protected function resetNumbers(): void
    {
        $this->selected_transporter = null;

        $this->total_income = 0;
        $this->total_cost_of_goods_sold = 0;
        $this->total_operating_expenses = 0;

        $this->gross_profit = 0;
        $this->gross_profit_percentage = 0;

        $this->net_profit = 0;
        $this->net_profit_percentage = 0;

        $this->total_trucks = 0;
        $this->total_trips = 0;
        $this->total_fuel_orders = 0;
        $this->total_fuel = 0;

        $this->total_truck_expenses = 0;
        $this->total_trailer_expenses = 0;
        $this->total_driver_expenses = 0;
        $this->total_other_expenses = 0;

        $this->fleetHorseIds = [];
        $this->fleetTrailerIds = [];
        $this->fleetDriverIds = [];
        $this->isThirdPartyTransporter = false;

        $this->cogs_items = [];
        $this->opex_items = [];
    }

    public function recalculate(): void
    {
        if (empty($this->selectedTransporter) || empty($this->from) || empty($this->to)) {
            $this->resetNumbers();
            return;
        }

        $transporterId = (int) $this->selectedTransporter;
        $this->selected_transporter = Transporter::find($transporterId);

        $this->fromDt = Carbon::parse($this->from)->startOfDay();
        $this->toDt   = Carbon::parse($this->to)->endOfDay();

        // This transporter's own fleet — a trip/bill counts for the transporter either when
        // it's tagged with transporter_id directly, OR when it references a horse/trailer/
        // driver that belongs to this transporter's fleet (most bills only set horse_id etc.,
        // they don't also stamp transporter_id).
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

        // Income in reporting currency (freight earned on this transporter's trips/trucks).
        // For real (non in-house) transporters, a trip that has "Add Transporter Freight"
        // checked and a transporter_freight amount set is billed to the transporter at that
        // rate, not the customer-facing freight — so use transporter_freight for those trips.
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

        // Expenses grouped by resource (truck / trailer / driver / other)
        $allItems = array_merge($this->cogs_items, $this->opex_items);
        $this->total_truck_expenses   = $this->sumByResource($allItems, 'Truck');
        $this->total_trailer_expenses = $this->sumByResource($allItems, 'Trailer');
        $this->total_driver_expenses  = $this->sumByResource($allItems, 'Driver');
        $this->total_other_expenses   = $this->sumByResource($allItems, 'Other');

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

    protected function sumByResource(array $items, string $resourceType): float
    {
        return array_sum(array_map(
            fn($x) => (float) ($x['amount'] ?? 0),
            array_filter($items, fn($x) => ($x['resource_type'] ?? 'Other') === $resourceType)
        ));
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

        // Join to prevent OR leakage and to allow trip-date filtering cleanly
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
            // The "Trip Expense - Transporter Payment" bill is what pays the transporter for
            // the trip — it's the transporter's income (already counted via transporter_freight
            // above), not an expense they incurred, so keep it out of their P&L expenses.
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

            // Amount in reporting currency
            $amount = ((int)($bill?->currency_id) === (int)$this->default_currency_id)
                ? (float) $be->subtotal_incl
                : (float) $be->exchange_amount;

            // Skip true zeros (optional)
            if (abs($amount) < 0.00001) {
                continue;
            }

            [$resourceType, $resourceName] = $this->resolveResource($bill);

            $items[] = [
                'date'            => $billDate,
                'bill_number'     => $bill?->bill_number ?? '',
                'trip_ref'        => $bill?->trip?->trip_number ?? ($bill?->trip_id ? ('Trip #' . $bill->trip_id) : ''),
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
        return view('livewire.transporters.profit-loss.index');
    }
}
