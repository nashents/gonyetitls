<?php

namespace App\Http\Livewire\Horses\ProfitLoss;


use Carbon\Carbon;
use App\Models\Fuel;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Trip;
use App\Models\Horse;
use Livewire\Component;
use App\Models\Assignment;
use App\Models\AccountType;
use App\Models\BillExpense;
use App\Models\TrailerAssignment;
use Illuminate\Support\Facades\Auth;

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

    // Flat line items (same format as Index)
    public $cogs_items = [];
    public $opex_items = [];

    // Summary by account for display in preview (Account => total)
    public $cogs_lines = []; // [ ['name'=>..., 'amount'=>...], ...]
    public $opex_lines = [];

    // Extra display fields (avoid DB queries in Blade)
    public $driver_name;
    public $trailer_numbers;

    public function mount($selectedHorse, $from, $to)
    {
        $this->company = Auth::user()->employee->company;

        $this->selectedHorse   = (int) $selectedHorse;
        $this->selected_horse  = Horse::findOrFail($this->selectedHorse);

        $this->from = $from;
        $this->to   = $to;

        $this->fromDt = Carbon::parse($from)->startOfDay();
        $this->toDt   = Carbon::parse($to)->endOfDay();

        $this->default_currency    = $this->company->currency;
        $this->default_currency_id = $this->company->currency_id;

        // Account groups
        $incomeType = AccountType::where('name', 'Income')->first();
        $cogsType   = AccountType::where('name', 'Cost Of Goods Sold')->first();
        $opexType   = AccountType::where('name', 'Operating Expense')->first();

        $this->income_accounts              = $incomeType?->accounts ?? collect();
        $this->cost_of_goods_sold_accounts  = $cogsType?->accounts ?? collect();
        $this->operating_expenses_accounts  = $opexType?->accounts ?? collect();

        // Driver / Trailer display (no blade queries)
        $assignment = Assignment::where('horse_id', $this->selectedHorse)->where('status', 1)->first();
        $this->driver_name = $assignment && $assignment->driver && $assignment->driver->employee
            ? trim(($assignment->driver->employee->name ?? '').' '.($assignment->driver->employee->surname ?? ''))
            : null;

        $trailerAssignment = TrailerAssignment::where('horse_id', $this->selectedHorse)->where('status', 1)->first();
        $this->trailer_numbers = $trailerAssignment && $trailerAssignment->trailer
            ? trim(($trailerAssignment->trailer->registration_number ?? '').($trailerAssignment->trailer->fleet_number ? ' ('.$trailerAssignment->trailer->fleet_number.')' : ''))
            : null;

        $this->recalculate();
    }

    public function recalculate(): void
    {
        $horseId = $this->selectedHorse;

        // ---------- Trips / Fuel stats ----------
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

        // ---------- Revenue (reporting currency) ----------
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
            ->sum('exchange_customer_freight'); // already in reporting currency

        $this->total_income = $incomeBase + $incomeFxBaseEquivalent;

        // ---------- Flat line items (same as Index) ----------
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

        // Summary lines by account (for your preview style)
        $this->cogs_lines = $this->groupItemsByAccount($this->cogs_items);
        $this->opex_lines = $this->groupItemsByAccount($this->opex_items);

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
        $horseId    = (int) $this->selectedHorse;
        $accountIds = $accounts->pluck('id')->values()->all();
        if (empty($accountIds)) return [];

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

            $amount = ((int)($bill?->currency_id) === (int)$this->default_currency_id)
                ? (float) $be->subtotal_incl
                : (float) $be->exchange_amount;

            if (abs($amount) < 0.00001) continue;

            $items[] = [
                'date'            => $billDate,
                'bill_number'     => $bill?->bill_number ?? '',
                'trip_ref'        => $bill?->trip?->trip_number ?? ($bill?->trip_id ? ('Trip #'.$bill->trip_id) : ''),
                'account_name'    => $be->account?->name ?? '—',
                'item_name'       => $this->resolveBillExpenseName($be),
                'expense_currency'=> $expenseCurrency,
                'amount'          => $amount, // reporting currency amount
            ];
        }

        return $items;
    }

    public function pdf($selectedHorse, $from, $to)
    {
        $horse = Horse::findOrFail($selectedHorse);

        $fromStr = \Carbon\Carbon::parse($from)->format('Y-m-d');
        $toStr   = \Carbon\Carbon::parse($to)->format('Y-m-d');

        $reg = preg_replace('/[^A-Za-z0-9_-]/', '_', $horse->registration_number);
        $filename = "{$reg}_{$fromStr}_{$toStr}_Profit_and_Loss.pdf";

        $pdf = PDF::loadView('horses.statement.pdf', compact('horse', 'from', 'to'));
        return $pdf->download($filename);   // or ->stream($filename)
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
        return view('livewire.horses.profit-loss.preview');
    }
}