<?php

namespace App\Http\Livewire\Dashboard;
use App\Models\Agent;
use App\Models\Allocation;
use App\Models\Assignment;
use App\Models\Attendance;
use App\Models\Bill;
use App\Models\BillExpense;
use App\Models\Booking;
use App\Models\Branch;
use App\Models\Breakdown;
use App\Models\Checklist;
use App\Models\Container;
use App\Models\Currency;
use App\Models\Customer;
use App\Models\DeliveryNote;
use App\Models\Department;
use App\Models\DepartmentHead;
use App\Models\Destination;
use App\Models\Dispatch;
use App\Models\Document;
use App\Models\Driver;
use App\Models\Employee;
use App\Models\Fitness;
use App\Models\Fuel;
use App\Models\Horse;
use App\Models\Inspection;
use App\Models\Inventory;
use App\Models\Invoice;
use App\Models\Leave;
use App\Models\Loan;
use App\Models\PayrollSalary;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Recovery;
use App\Models\Requisition;
use App\Models\Service;
use App\Models\Shift;
use App\Models\Ticket;
use App\Models\Trailer;
use App\Models\Transporter;
use App\Models\TransportOrder;
use App\Models\Trip;
use App\Models\Tyre;
use App\Models\Vehicle;
use App\Models\Vendor;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Index extends Component
{

   
    private const MONTH_ABBR = [
        1 => 'jan', 2 => 'feb', 3 => 'mar', 4 => 'apr', 5 => 'may', 6 => 'jun',
        7 => 'jul', 8 => 'aug', 9 => 'sep', 10 => 'oct', 11 => 'nov', 12 => 'dec',
    ];

    private const REVENUE_YEARS = [2021, 2022, 2023, 2024, 2025, 2026];

    // ── Context ─────────────────────────────────────────────────────────────
    public $user, $employee, $driver, $company, $company_currency;
    public $selectedCurrency, $currency_name, $currencies;
    public $last_refreshed_at;
    public $year;
    public $chartData;
    public $chartVolumeData;

    // ── Legacy monthly series (kept for existing Highcharts) ─────────────
    public $jan_litreage_loss, $feb_litreage_loss, $mar_litreage_loss, $apr_litreage_loss, $may_litreage_loss, $jun_litreage_loss, $jul_litreage_loss, $aug_litreage_loss, $sep_litreage_loss, $oct_litreage_loss, $nov_litreage_loss, $dec_litreage_loss;
    public $jan_weight_loss, $feb_weight_loss, $mar_weight_loss, $apr_weight_loss, $may_weight_loss, $jun_weight_loss, $jul_weight_loss, $aug_weight_loss, $sep_weight_loss, $oct_weight_loss, $nov_weight_loss, $dec_weight_loss;
    public $jan_distance, $feb_distance, $mar_distance, $apr_distance, $may_distance, $jun_distance, $jul_distance, $aug_distance, $sep_distance, $oct_distance, $nov_distance, $dec_distance;
    public $jan_trips, $feb_trips, $mar_trips, $apr_trips, $may_trips, $jun_trips, $jul_trips, $aug_trips, $sep_trips, $oct_trips, $nov_trips, $dec_trips;
    public $jan_sales, $feb_sales, $mar_sales, $apr_sales, $may_sales, $jun_sales, $jul_sales, $aug_sales, $sep_sales, $oct_sales, $nov_sales, $dec_sales;
    public $jan_litreage, $feb_litreage, $mar_litreage, $apr_litreage, $may_litreage, $jun_litreage, $jul_litreage, $aug_litreage, $sep_litreage, $oct_litreage, $nov_litreage, $dec_litreage;
    public $jan_weight, $feb_weight, $mar_weight, $apr_weight, $may_weight, $jun_weight, $jul_weight, $aug_weight, $sep_weight, $oct_weight, $nov_weight, $dec_weight;
    public $jan_open_bookings, $feb_open_bookings, $mar_open_bookings, $apr_open_bookings, $may_open_bookings, $jun_open_bookings, $jul_open_bookings, $aug_open_bookings, $sep_open_bookings, $oct_open_bookings, $nov_open_bookings, $dec_open_bookings;
    public $jan_closed_bookings, $feb_closed_bookings, $mar_closed_bookings, $apr_closed_bookings, $may_closed_bookings, $jun_closed_bookings, $jul_closed_bookings, $aug_closed_bookings, $sep_closed_bookings, $oct_closed_bookings, $nov_closed_bookings, $dec_closed_bookings;
    public $jan_initial_fuel, $feb_initial_fuel, $mar_initial_fuel, $apr_initial_fuel, $may_initial_fuel, $jun_initial_fuel, $jul_initial_fuel, $aug_initial_fuel, $sep_initial_fuel, $oct_initial_fuel, $nov_initial_fuel, $dec_initial_fuel;
    public $jan_topup_fuel, $feb_topup_fuel, $mar_topup_fuel, $apr_topup_fuel, $may_topup_fuel, $jun_topup_fuel, $jul_topup_fuel, $aug_topup_fuel, $sep_topup_fuel, $oct_topup_fuel, $nov_topup_fuel, $dec_topup_fuel;
    public $jan, $feb, $mar, $apr, $may, $jun, $jul, $aug, $sep, $oct, $nov, $dec;
    public $jan_expense, $feb_expense, $mar_expense, $apr_expense, $may_expense, $jun_expense, $jul_expense, $aug_expense, $sep_expense, $oct_expense, $nov_expense, $dec_expense;
    public $income_2021, $income_2022, $income_2023, $income_2024, $income_2025, $income_2026;
    public $expenses_2021, $expenses_2022, $expenses_2023, $expenses_2024, $expenses_2025, $expenses_2026;

    // ── Legacy counts (kept for existing sections) ───────────────────────
    public $shift_count, $transporter_count, $agent_count, $department_count;
    public $trip_count, $horse_count, $destinations_count, $trailer_count;
    public $vendor_count, $vehicle_count, $assignment_count, $branch_count;
    public $customer_count, $bill_count, $invoice_count, $employee_count;
    public $attendance_count, $driver_count, $leave_count, $tyre_count;
    public $service_count, $inventory_count, $booking_count, $ticket_count;
    public $fuel_supplier_count, $fuel_order_count, $transport_order_count;
    public $inventory_purchases_count, $inventory_dispatches_count;
    public $workshop_inspection_count, $my_inspections_count, $my_tickets_count;
    public $inspection_count, $product_count;
    public $driver_inspections, $driver_recoveries, $driver_trips, $driver_breakdowns;
    public $transporters, $agents, $hods, $recent_employees;
    public $containers, $allocations, $myallocations, $trips, $bookings, $fuel_orders;
    public $top_drivers, $top_horses;
    public $petrol_quantity, $diesel_quantity;
    public $males, $females;
    public $resignation_2022, $resignation_2023, $resignation_2024, $resignation_2025;

    // ═══════════════════════════════════════════════════════════════════════
    // NEW KPI PROPERTIES
    // ═══════════════════════════════════════════════════════════════════════

    // ROW 1 · Alert Flags
    public $overdue_invoices_count        = 0;
    public $overdue_invoices_value        = 0;
    public $expiring_documents_count      = 0;
    public $expired_documents_count       = 0;
    public $expired_reminders_count       = 0;
    public $trips_overdue_count           = 0;
    public $pending_authorizations_count  = 0;
    public $overdue_services_count        = 0;
    public $vehicles_on_breakdown_count   = 0;

    // ROW 2 · Executive Financial KPIs
    public $revenue_today          = 0;
    public $revenue_mtd            = 0;
    public $revenue_ytd            = 0;
    public $revenue_prev_month     = 0;
    public $revenue_mtd_change_pct = 0;
    public $gross_profit_mtd       = 0;
    public $gross_margin_pct       = 0;
    public $cost_of_sales_mtd      = 0;
    public $outstanding_invoices_count = 0;
    public $outstanding_invoices_value = 0;
    public $collections_mtd            = 0;
    public $outstanding_bills_value    = 0;
    public $bills_due_this_week        = 0;
    public $overdue_bills_value        = 0;
    public $cash_position              = 0;
    public $diesel_balance_litres      = 0;
    public $petrol_balance_litres      = 0;

    // ROW 3 · Transport Operations
    public $trips_planned_today      = 0;
    public $trips_in_progress        = 0;
    public $trips_completed_today    = 0;
    public $trips_cancelled_mtd      = 0;
    public $trips_mtd                = 0;
    public $tonnes_today             = 0;
    public $tonnes_mtd               = 0;
    public $avg_load_factor          = 0;
    public $loads_today              = 0;
    public $loads_mtd                = 0;
    public $km_today                 = 0;
    public $km_mtd                   = 0;
    public $avg_km_per_horse         = 0;
    public $transport_orders_pending = 0;
    public $transport_orders_mtd     = 0;

    // ROW 4 · Fleet
    public $fleet_active             = 0;
    public $fleet_in_workshop        = 0;
    public $fleet_on_breakdown       = 0;
    public $fleet_idle               = 0;
    public $fleet_total              = 0;
    public $fleet_utilization_pct    = 0;
    public $trailers_active          = 0;
    public $trailers_in_workshop     = 0;
    public $trailers_total           = 0;
    public $trailer_utilization_pct  = 0;
    public $docs_expiring_30d        = 0;
    public $docs_expired             = 0;
    public $reminders_expiring_7d         = 0;
    public $vehicles_due_service     = 0;
    public $vehicles_overdue_service = 0;

    // ROW 5 · Fuel
    public $fuel_issued_today        = 0;
    public $fuel_issued_mtd          = 0;
    public $fuel_cost_mtd            = 0;
    public $avg_litres_per_km        = 0;
    public $fuel_exceptions_count    = 0;

    // ROW 6 · Workshop
    public $open_job_cards           = 0;
    public $completed_job_cards_mtd  = 0;
    public $overdue_repairs_count    = 0;
    public $workshop_spend_mtd       = 0;
    public $breakdowns_mtd           = 0;

    // ROW 7 · Inventory
    public $inventory_total_value    = 0;
    public $low_stock_count          = 0;
    public $pending_pos_count        = 0;
    public $pending_requisitions     = 0;
    public $active_tyres             = 0;

    // ROW 8 · HR
    public $active_employees         = 0;
    public $employees_on_leave       = 0;
    public $active_drivers           = 0;
    public $drivers_docs_expiring    = 0;
    public $pending_leave_requests   = 0;
    public $payroll_cost_mtd         = 0;
    public $outstanding_loans        = 0;

    // ROW 9 · Pending Authorizations breakdown
    public $pending_trips_auth        = 0;
    public $pending_invoices_auth     = 0;
    public $pending_bills_auth        = 0;
    public $pending_fuel_auth         = 0;
    public $pending_requisitions_auth = 0;
    public $pending_leave_auth        = 0;
    public $pending_purchase_auth     = 0;
    public $pending_bookings_auth     = 0;

    // Sparkline data (last 7 days)
    public $sparkline_revenue = [];
    public $sparkline_trips   = [];
    public $sparkline_fuel    = [];

    // ═══════════════════════════════════════════════════════════════════════

    public function mount(): void
    {
        $this->user     = Auth::user();
        $this->employee = $this->user->employee;
        $this->driver   = $this->employee->driver ?? null;
        $this->company  = $this->employee->company;
        $this->company_currency = $this->company->currency ?? null;

        $this->selectedCurrency = $this->company_currency->id ?? 1;
        $this->currency_name    = $this->company_currency->name ?? 'USD';
        $this->year             = now()->year;
        $this->last_refreshed_at = now()->format('d M Y H:i');

        $this->loadAllKpis();
        $this->loadChart();
       
    }

    public function updatedYear(): void
    {
        $this->loadChart();
        $this->emit('drivers-weight-updated', $this->chartData, $this->year);
        $this->emit('drivers-volume-updated', $this->chartVolumeData, $this->year);
    }

    // ═══════════════════════════════════════════════════════════════════════
    // MASTER LOADER
    // ═══════════════════════════════════════════════════════════════════════

    private function loadAllKpis(): void
    {
        $companyId = $this->company->id ?? null;
        $cacheKey  = "dashboard_kpis_{$companyId}_{$this->selectedCurrency}_" . now()->format('YmdH') . floor(now()->minute / 10);

        $kpis = Cache::remember($cacheKey, 600, function () use ($companyId) {
            return $this->computeAllKpis($companyId);
        });

        foreach ($kpis as $key => $value) {
            if (property_exists($this, $key)) {
                $this->{$key} = $value;
            }
        }

        $this->loadAggregates();
        $this->loadCounts();
        $this->loadLists();
    }

    // ═══════════════════════════════════════════════════════════════════════
    // COMPUTED KPIs
    // ═══════════════════════════════════════════════════════════════════════

    private function computeAllKpis(int|null $companyId): array
    {
        $now            = Carbon::now();
        $today          = $now->toDateString();
        $monthStart     = $now->copy()->startOfMonth()->toDateString();
        $monthEnd       = $now->copy()->endOfMonth()->toDateString();
        $yearStart      = $now->copy()->startOfYear()->toDateString();
        $prevMonthStart = $now->copy()->subMonth()->startOfMonth()->toDateString();
        $prevMonthEnd   = $now->copy()->subMonth()->endOfMonth()->toDateString();
        $in30d          = $now->copy()->addDays(30)->toDateString();
        $in7d           = $now->copy()->addDays(7)->toDateString();
        $weekEnd        = $now->copy()->endOfWeek()->toDateString();
        $cur            = $this->selectedCurrency;

        $kpis = [];

        // ── FINANCIAL ───────────────────────────────────────────────────

        $kpis['revenue_today'] = (float) Invoice::where('authorization', 'approved')
            ->where('currency_id', $cur)->whereDate('date', $today)
            ->sum(DB::raw('COALESCE(total+0,0)'));

        $kpis['revenue_mtd'] = (float) Invoice::where('authorization', 'approved')
        ->where('currency_id', $cur)
        ->whereBetween('date', [$monthStart, $today])
        ->sum('total');

        $kpis['revenue_ytd'] = (float) Invoice::where('authorization', 'approved')
            ->where('currency_id', $cur)->whereBetween('date', [$yearStart, $today])
            ->sum(DB::raw('COALESCE(total+0,0)'));

        $kpis['revenue_prev_month'] = (float) Invoice::where('authorization', 'approved')
            ->where('currency_id', $cur)->whereBetween('date', [$prevMonthStart, $prevMonthEnd])
            ->sum(DB::raw('COALESCE(total+0,0)'));

        $kpis['revenue_mtd_change_pct'] = $kpis['revenue_prev_month'] > 0
            ? round((($kpis['revenue_mtd'] - $kpis['revenue_prev_month']) / $kpis['revenue_prev_month']) * 100, 1)
            : 0;

            
        $kpis['cost_of_sales_mtd'] = (float) BillExpense::whereHas('account.account_type', function ($query) {
            $query->where('name', 'Costs Of Goods Sold');
        })
        ->whereHas('bill', function ($query) use ($monthStart, $today, $cur) {
            $query->whereBetween('bill_date', [$monthStart, $today])->where('currency_id', $cur);
        })
        ->sum(DB::raw('COALESCE(subtotal_incl+0,0)'));

       $kpis['gross_profit_mtd'] = $kpis['revenue_mtd'] - $kpis['cost_of_sales_mtd'];


        $kpis['gross_margin_pct'] = $kpis['revenue_mtd'] > 0
            ? round(($kpis['gross_profit_mtd'] / $kpis['revenue_mtd']) * 100, 1)
            : 0;

        $kpis['collections_mtd'] = (float) DB::table('invoice_payments')
            ->join('invoices', 'invoice_payments.invoice_id', '=', 'invoices.id')
            ->where('invoices.currency_id', $cur)
            ->whereBetween('invoice_payments.created_at', [$monthStart.' 00:00:00', $monthEnd.' 23:59:59'])
            ->whereNull('invoice_payments.deleted_at')
            ->sum(DB::raw('COALESCE(invoice_payments.amount+0,0)'));

        $oi = Invoice::where('authorization', 'approved')->where('currency_id', $cur)
            ->where('status', '!=', 'Paid')->whereNull('deleted_at')
            ->selectRaw('COUNT(*) as cnt, SUM(COALESCE(balance+0,0)) as val')->first();
            
        $kpis['outstanding_invoices_count'] = (int)   ($oi->cnt ?? 0);

        $kpis['outstanding_invoices_value'] = (float) ($oi->val ?? 0);

        $ov = Invoice::where('authorization', 'approved')->where('currency_id', $cur)
            ->where('status', '!=', 'Paid')->where('expiry', '<', $today)
            ->whereNotNull('expiry')->whereNull('deleted_at')
            ->selectRaw('COUNT(*) as cnt, SUM(COALESCE(balance+0,0)) as val')->first();
        $kpis['overdue_invoices_count'] = (int)   ($ov->cnt ?? 0);
        $kpis['overdue_invoices_value'] = (float) ($ov->val ?? 0);

        $kpis['outstanding_bills_value'] = (float) Bill::where('authorization', 'approved')
            ->where('to_be_paid', true)
            ->where('currency_id', $cur)->where('status', '!=', 'Paid')->whereNull('deleted_at')
            ->sum(DB::raw('COALESCE(balance+0,0)'));

        $kpis['bills_due_this_week'] = (float) Bill::where('authorization', 'approved')
            ->where('currency_id', $cur)->where('status', '!=', 'Paid')
            ->whereBetween('due_date', [$today, $weekEnd])->whereNull('deleted_at')
            ->sum(DB::raw('COALESCE(balance+0,0)'));

        $kpis['overdue_bills_value'] = (float) Bill::where('authorization', 'approved')
            ->where('currency_id', $cur)->where('status', '!=', 'Paid')
            ->where('due_date', '<', $today)->whereNotNull('due_date')
            ->whereNull('deleted_at')
            ->sum(DB::raw('COALESCE(balance+0,0)'));

        $billsPaidMtd = (float) DB::table('bill_payments')
            ->join('bills', 'bill_payments.bill_id', '=', 'bills.id')
            ->where('bills.currency_id', $cur)
            ->whereBetween('bill_payments.created_at', [$monthStart.' 00:00:00', $monthEnd.' 23:59:59'])
            ->whereNull('bill_payments.deleted_at')
            ->sum(DB::raw('COALESCE(bill_payments.amount+0,0)'));
        $kpis['cash_position'] = $kpis['collections_mtd'] - $billsPaidMtd;

        $fs = Container::selectRaw("SUM(CASE WHEN fuel_type='Diesel' THEN COALESCE(balance+0,0) ELSE 0 END) as diesel, SUM(CASE WHEN fuel_type='Petrol' THEN COALESCE(balance+0,0) ELSE 0 END) as petrol")->first();
        $kpis['diesel_balance_litres'] = (float) ($fs->diesel ?? 0);
        $kpis['petrol_balance_litres'] = (float) ($fs->petrol ?? 0);

        // ── TRANSPORT OPERATIONS ─────────────────────────────────────────

        $kpis['trips_planned_today'] = Trip::whereNull('deleted_at')
            ->whereDate('start_date', $today)
            ->whereNotIn('trip_status', ['Cancelled','Offloaded','Enroute','Loaded'])
            ->count();

        $kpis['trips_in_progress'] = Trip::whereNull('deleted_at')
            ->whereIn('trip_status', ['Enroute','Loaded','In Progress'])
            ->count();

        $kpis['trips_completed_today'] = Trip::whereNull('deleted_at')
            ->where('trip_status', 'Offloaded')->whereDate('end_date', $today)
            ->count();

        $kpis['trips_cancelled_mtd'] = Trip::whereNull('deleted_at')
            ->where('trip_status', 'Cancelled')
            ->whereBetween('start_date', [$monthStart, $monthEnd])
            ->count();

        $kpis['trips_mtd'] = Trip::whereNull('deleted_at')
            ->whereBetween('start_date', [$monthStart, $monthEnd])->count();

        $kpis['trips_overdue_count'] = Trip::whereNull('deleted_at')
            ->whereIn('trip_status', ['Started','Loading Point','Loaded','Intransit','Onhold','Offloading Point'])
            ->whereNotNull('end_date')->where('end_date', '<', $today)
            ->count();

        $kpis['tonnes_today'] = (float) Trip::whereNull('deleted_at')
            ->whereDate('start_date', $today)->sum(DB::raw('COALESCE(weight+0,0)'));

        $kpis['tonnes_mtd'] = (float) Trip::whereNull('deleted_at')
            ->whereBetween('start_date', [$monthStart, $monthEnd])
            ->sum(DB::raw('COALESCE(weight+0,0)'));

        $kpis['loads_today'] = Trip::whereNull('deleted_at')
            ->whereDate('start_date', $today)->where('trip_status', '!=', 'Cancelled')->count();

        $kpis['loads_mtd'] = Trip::whereNull('deleted_at')
            ->whereBetween('start_date', [$monthStart, $monthEnd])
            ->where('trip_status', '!=', 'Cancelled')->count();

        $kmT = Trip::whereNull('deleted_at')->whereDate('start_date', $today)
            ->whereNotNull('starting_mileage')->where('starting_mileage','!=','')
            ->whereNotNull('ending_mileage')->where('ending_mileage','!=','')
            ->selectRaw('SUM((ending_mileage+0)-(starting_mileage+0)) as km')->first();
        $kpis['km_today'] = (float) ($kmT->km ?? 0);

        $kmM = Trip::whereNull('deleted_at')->whereBetween('start_date', [$monthStart, $monthEnd])
            ->whereNotNull('starting_mileage')->where('starting_mileage','!=','')
            ->whereNotNull('ending_mileage')->where('ending_mileage','!=','')
            ->selectRaw('SUM((ending_mileage+0)-(starting_mileage+0)) as km')->first();
        $kpis['km_mtd'] = (float) ($kmM->km ?? 0);

        $ah = Horse::where('archive', false)->count();
        $kpis['avg_km_per_horse'] = $ah > 0 ? round($kpis['km_mtd'] / $ah, 0) : 0;

        $kpis['transport_orders_pending'] = TransportOrder::whereNull('deleted_at')
            ->where('authorization', 'pending')->count();
        $kpis['transport_orders_mtd'] = TransportOrder::whereNull('deleted_at')
            ->whereBetween('created_at', [$monthStart.' 00:00:00', $monthEnd.' 23:59:59'])->count();

        // ── FLEET ────────────────────────────────────────────────────────

        $totalHorses = Horse::where('archive', false)->count();
        $kpis['fleet_total'] = $totalHorses;

        

        $baseQuery = Booking::whereNull('deleted_at')
            ->where('authorization', 'approved');
        $kpis['fleet_in_workshop'] =
            (clone $baseQuery)->whereNotNull('horse_id')->distinct('horse_id')->count('horse_id')
            + (clone $baseQuery)->whereNotNull('vehicle_id')->distinct('vehicle_id')->count('vehicle_id')
            + (clone $baseQuery)->whereNotNull('trailer_id')->distinct('trailer_id')->count('trailer_id');

        $basebreakdownQuery = Breakdown::query();

        $kpis['fleet_on_breakdown'] =
            (clone $basebreakdownQuery)->whereNotNull('horse_id')->distinct('horse_id')->count('horse_id')
            + (clone $basebreakdownQuery)->whereNotNull('vehicle_id')->distinct('vehicle_id')->count('vehicle_id')
            + (clone $basebreakdownQuery)->whereNotNull('trailer_id')->distinct('trailer_id')->count('trailer_id');

        $kpis['vehicles_on_breakdown_count'] = $kpis['fleet_on_breakdown'];

        $kpis['fleet_active'] = Trip::whereNull('deleted_at')
            ->whereNotIn('trip_status',['Scheduled','Cancelled','Onhold',])
            ->whereNotNull('horse_id')->distinct('horse_id')->count('horse_id');

        $kpis['fleet_idle'] = max(0, $totalHorses
            - $kpis['fleet_active']
            - $kpis['fleet_in_workshop']);

        $kpis['fleet_utilization_pct'] = $totalHorses > 0
            ? round(($kpis['fleet_active'] / $totalHorses) * 100, 1) : 0;

        $totalTrailers = Trailer::where('archive', false)->count();
        $kpis['trailers_total'] = $totalTrailers;
        $kpis['trailers_in_workshop'] = Trailer::where('archive', false)
            ->whereHas('bookings', fn ($q) => $q->where('authorization','approved')
                ->where('status',1)->whereNull('out_date'))
            ->count();
        $kpis['trailers_active'] = DB::table('trailer_trip')
            ->join('trips','trips.id','=','trailer_trip.trip_id')
            ->whereNull('trips.deleted_at')
            ->whereIn('trips.trip_status',['Enroute','Loaded','In Progress'])
            ->whereNull('trailer_trip.deleted_at')
            ->distinct('trailer_trip.trailer_id')
            ->count('trailer_trip.trailer_id');
        $kpis['trailer_utilization_pct'] = $totalTrailers > 0
            ? round(($kpis['trailers_active'] / $totalTrailers) * 100, 1) : 0;

        $kpis['reminders_expiring_7d'] = Fitness::whereNull('deleted_at')
            ->where('closed',0)->whereBetween('expires_at', [$today, $in7d])->count();
        $kpis['docs_expiring_30d'] = Fitness::whereNull('deleted_at')
            ->where('closed',0)->whereBetween('expires_at', [$today, $in30d])->count();
        $kpis['reminders_expired'] = Fitness::whereNull('deleted_at')
            ->where('closed',0)->where('expires_at','<',$today)->count();
        $kpis['docs_expired'] = Document::whereNull('deleted_at')
            ->where('expires_at','<',$today)->count();

        $kpis['expiring_documents_count'] = $kpis['docs_expiring_30d'];
        $kpis['expired_documents_count']  = $kpis['docs_expired'];
        $kpis['expired_reminders_count']  = $kpis['reminders_expired'];

        $kpis['vehicles_due_service'] = Service::whereNull('deleted_at')
            ->whereBetween('start_date', [$today, $in7d])->count();
        $kpis['vehicles_overdue_service'] = Service::whereNull('deleted_at')
            ->where('start_date','<',$today)->count();

        // ── FUEL ─────────────────────────────────────────────────────────

        $kpis['fuel_issued_today'] = (float) Fuel::whereNull('deleted_at')
            ->whereDate('date',$today)->where('authorization','approved')
            ->sum(DB::raw('COALESCE(quantity+0,0)'));

        $kpis['fuel_issued_mtd'] = (float) Fuel::whereNull('deleted_at')
            ->whereBetween('date',[$monthStart,$monthEnd])->where('authorization','approved')
            ->sum(DB::raw('COALESCE(quantity+0,0)'));

        $kpis['fuel_cost_mtd'] = (float) Fuel::whereNull('deleted_at')
            ->whereBetween('date',[$monthStart,$monthEnd])->where('authorization','approved')
            ->sum(DB::raw('COALESCE(amount+0,0)'));

        $kpis['avg_litres_per_km'] = $kpis['km_mtd'] > 0
            ? round($kpis['fuel_issued_mtd'] / $kpis['km_mtd'], 2) : 0;

        $kpis['fuel_exceptions_count'] = Fuel::whereNull('deleted_at')
            ->whereBetween('date',[$monthStart,$monthEnd])
            ->whereNull('trip_id')->where('authorization','approved')->count();

        // ── WORKSHOP ─────────────────────────────────────────────────────

        $kpis['open_job_cards'] = Booking::whereNull('deleted_at')
            ->where('authorization','approved')->where('status',1)->whereNull('out_date')->count();

        $kpis['overdue_repairs_count'] = Booking::whereNull('deleted_at')
            ->where('authorization','approved')->where('status',1)->whereNull('out_date')
            ->whereNotNull('estimated_out_date')->where('estimated_out_date','<',$today)->count();

        $kpis['completed_job_cards_mtd'] = Booking::whereNull('deleted_at')
            ->where('authorization','approved')->where('status',0)
            ->whereBetween('out_date',[$monthStart,$monthEnd])->count();

        $kpis['workshop_spend_mtd'] = (float) Bill::whereNull('deleted_at')
            ->where('currency_id',$cur)->whereNotNull('ticket_id')
            ->whereBetween('bill_date',[$monthStart,$monthEnd])
            ->sum(DB::raw('COALESCE(total+0,0)'));

        $kpis['breakdowns_mtd'] = Breakdown::whereNull('deleted_at')
            ->whereBetween('date',[$monthStart,$monthEnd])->count();

        $kpis['overdue_services_count'] = $kpis['overdue_repairs_count'];

        // ── INVENTORY ────────────────────────────────────────────────────

        $kpis['inventory_total_value'] = (float) DB::table('inventories')
            ->whereNull('deleted_at')->where('disposed',0)->where('status',1)
            ->sum(DB::raw('COALESCE(balance+0,0) * COALESCE(total+0,0)'));

        $kpis['low_stock_count'] = DB::table('products')
        ->leftJoin('inventories', function ($join) {
            $join->on('products.id', '=', 'inventories.product_id')
                ->whereNull('inventories.deleted_at')
                ->where('inventories.disposed', 0)
                ->where('inventories.status', 1);
        })
        ->select(
            'products.id',
            'products.min',
            DB::raw('COALESCE(SUM(COALESCE(inventories.qty + 0, 0)), 0) as balance')
        )
        ->groupBy('products.id', 'products.min')
        ->havingRaw('balance > 0')
        ->havingRaw('balance <= products.min')
        ->count();

        $kpis['pending_pos_count'] = Purchase::whereNull('deleted_at')
            ->where('authorization','pending')->where('department','inventory')->count();

        $kpis['pending_requisitions'] = Requisition::whereNull('deleted_at')
            ->where('authorization','pending')->count();

        $kpis['active_tyres'] = Tyre::whereNull('deleted_at')
            ->where('disposed',0)->where('status',1)->count();

        // ── HR ───────────────────────────────────────────────────────────

        $kpis['active_employees'] = Employee::whereNull('deleted_at')
            ->where('archive',false)->count();

        $kpis['employees_on_leave'] = Leave::whereNull('deleted_at')
            ->where('status','approved')->where('from','<=',$today)->where('to','>=',$today)
            ->distinct('employee_id')->count('employee_id');

        $kpis['active_drivers'] = Driver::whereNull('deleted_at')
            ->where('archive',false)->where('status',1)->count();

        $kpis['drivers_docs_expiring'] = Fitness::whereNull('deleted_at')
            ->whereNotNull('employee_id')->whereBetween('expires_at',[$today,$in30d])
            ->where('closed',0)->count();

        $kpis['pending_leave_requests'] = Leave::whereNull('deleted_at')
            ->where('status','pending')->count();

        $kpis['payroll_cost_mtd'] = (float) PayrollSalary::whereNull('deleted_at')
            ->whereHas('payroll', fn ($q) => $q->where('authorization','approved')
                ->where('month', Carbon::now()->format('F'))
                ->where('year', (string) Carbon::now()->year))
            ->sum(DB::raw('COALESCE(net+0,0)'));

        $kpis['outstanding_loans'] = (float) Loan::whereNull('deleted_at')
        ->where('currency_id',$cur)
            ->where('authorization','approved')->where('status','Unpaid')
            ->sum(DB::raw('COALESCE(balance+0,0)'));

        // ── PENDING AUTHORIZATIONS ────────────────────────────────────────

        $kpis['pending_trips_auth']        = Trip::whereNull('deleted_at')->where('authorization','pending')->count();
        $kpis['pending_invoices_auth']     = Invoice::whereNull('deleted_at')->where('authorization','pending')->count();
        $kpis['pending_bills_auth']        = Bill::whereNull('deleted_at')->where('authorization','pending')->count();
        $kpis['pending_fuel_auth']         = Fuel::whereNull('deleted_at')->where('authorization','pending')->count();
        $kpis['pending_requisitions_auth'] = Requisition::whereNull('deleted_at')->where('authorization','pending')->count();
        $kpis['pending_leave_auth']        = Leave::whereNull('deleted_at')->where('status','pending')->count();
        $kpis['pending_purchase_auth']     = Purchase::whereNull('deleted_at')->where('authorization','pending')->count();
        $kpis['pending_bookings_auth']     = Booking::whereNull('deleted_at')->where('authorization','pending')->count();

        $kpis['pending_authorizations_count'] =
            $kpis['pending_trips_auth'] + $kpis['pending_invoices_auth'] +
            $kpis['pending_bills_auth'] + $kpis['pending_fuel_auth'] +
            $kpis['pending_requisitions_auth'] + $kpis['pending_leave_auth'] +
            $kpis['pending_purchase_auth'] + $kpis['pending_bookings_auth'];

        // ── SPARKLINES (last 7 days) ──────────────────────────────────────

        $sparkRevenue = $sparkTrips = $sparkFuel = [];
        for ($i = 6; $i >= 0; $i--) {
            $d = Carbon::now()->subDays($i)->toDateString();
            $sparkRevenue[] = (float) Invoice::where('authorization','approved')
                ->where('currency_id',$cur)->whereDate('date',$d)
                ->sum(DB::raw('COALESCE(total+0,0)'));
            $sparkTrips[] = (int) Trip::whereNull('deleted_at')->whereDate('start_date',$d)->count();
            $sparkFuel[]  = (float) Fuel::whereNull('deleted_at')->whereDate('date',$d)
                ->where('authorization','approved')->sum(DB::raw('COALESCE(quantity+0,0)'));
        }
        $kpis['sparkline_revenue'] = $sparkRevenue;
        $kpis['sparkline_trips']   = $sparkTrips;
        $kpis['sparkline_fuel']    = $sparkFuel;

        return $kpis;
    }

    // ═══════════════════════════════════════════════════════════════════════
    // LEGACY METHODS — preserved for existing Highcharts
    // ═══════════════════════════════════════════════════════════════════════

    private function pick($keyed, string $col): array
    {
        $out = array_fill(1, 12, 0);
        foreach ($keyed as $m => $row) {
            $out[(int) $m] = (float) ($row->{$col} ?? 0);
        }
        return $out;
    }

    private function spread(string $pattern, array $buckets): void
    {
        foreach (self::MONTH_ABBR as $m => $abbr) {
            $this->{sprintf($pattern, $abbr)} = $buckets[$m] ?? 0;
        }
    }

    private function loadAggregates(): void
    {
        $year = (int) date('Y');

        $dn = DeliveryNote::whereYear('offloaded_date', $year)
            ->selectRaw('MONTH(offloaded_date) m, SUM(loaded_litreage_at_20+0) litreage, SUM(loaded_weight+0) weight')
            ->groupBy('m')->get()->keyBy('m');
        $this->spread('%s_litreage', $this->pick($dn, 'litreage'));
        $this->spread('%s_weight',   $this->pick($dn, 'weight'));

        $litLoss = DeliveryNote::whereYear('offloaded_date', $year)
            ->whereNotNull('loaded_litreage_at_20')->where('loaded_litreage_at_20','!=','')
            ->whereNotNull('offloaded_litreage_at_20')->where('offloaded_litreage_at_20','!=','')
            ->selectRaw('MONTH(offloaded_date) m, SUM((loaded_litreage_at_20+0)-(offloaded_litreage_at_20+0)) v')
            ->groupBy('m')->get()->keyBy('m');
        $this->spread('%s_litreage_loss', $this->pick($litLoss, 'v'));

        $wLoss = DeliveryNote::whereYear('offloaded_date', $year)
            ->whereNotNull('loaded_weight')->where('loaded_weight','!=','')
            ->whereNotNull('offloaded_weight')->where('offloaded_weight','!=','')
            ->selectRaw('MONTH(offloaded_date) m, SUM((loaded_weight+0)-(offloaded_weight+0)) v')
            ->groupBy('m')->get()->keyBy('m');
        $this->spread('%s_weight_loss', $this->pick($wLoss, 'v'));

        $trips = Trip::whereYear('start_date', $year)
            ->selectRaw('MONTH(start_date) m, COUNT(*) cnt, SUM(freight+0) sales')
            ->groupBy('m')->get()->keyBy('m');
        $this->spread('%s_trips', $this->pick($trips, 'cnt'));
        $this->spread('%s_sales', $this->pick($trips, 'sales'));

        $dist = Trip::whereYear('start_date', $year)
            ->whereNotNull('starting_mileage')->where('starting_mileage','!=','')
            ->whereNotNull('ending_mileage')->where('ending_mileage','!=','')
            ->selectRaw('MONTH(start_date) m, SUM((ending_mileage+0)-(starting_mileage+0)) v')
            ->groupBy('m')->get()->keyBy('m');
        $this->spread('%s_distance', $this->pick($dist, 'v'));

        $fuel = Fuel::whereYear('created_at', $year)->whereIn('fillup', [0,1])
            ->selectRaw('MONTH(created_at) m, fillup, SUM(quantity+0) q')
            ->groupBy('m','fillup')->get();
        $initial = $topup = array_fill(1, 12, 0);
        foreach ($fuel as $r) {
            $m = (int) $r->m;
            $r->fillup == 1 ? $initial[$m] = (float)$r->q : $topup[$m] = (float)$r->q;
        }
        $this->spread('%s_initial_fuel', $initial);
        $this->spread('%s_topup_fuel',   $topup);

        $bk = Booking::whereYear('created_at', $year)
            ->selectRaw('MONTH(created_at) m, status, COUNT(*) c')
            ->groupBy('m','status')->get();
        $open = $closed = array_fill(1, 12, 0);
        foreach ($bk as $r) {
            $m = (int) $r->m;
            if ($r->status == 1) $open[$m] = (int)$r->c;
            elseif ($r->status == 0) $closed[$m] = (int)$r->c;
        }
        $this->spread('%s_open_bookings',   $open);
        $this->spread('%s_closed_bookings', $closed);

        $res = Employee::whereNotNull('end_date')
            ->selectRaw('YEAR(end_date) y, COUNT(*) c')->groupBy('y')->pluck('c','y');
        foreach ([2022,2023,2024,2025] as $y) {
            $this->{'resignation_'.$y} = (int)($res[$y] ?? 0);
        }

        $g = Employee::where('status',1)
            ->selectRaw("SUM(gender LIKE '%Male%') males, SUM(gender LIKE '%Female%') females")
            ->first();
        $this->males   = (int)($g->males   ?? 0);
        $this->females = (int)($g->females ?? 0);

        $this->petrol_quantity = Container::where('fuel_type','Petrol')->sum('balance');
        $this->diesel_quantity = Container::where('fuel_type','Diesel')->sum('balance');
    }

    private function loadCounts(): void
    {
        $year = date('Y');

        $this->shift_count              = Shift::count();
        $this->transporter_count        = Transporter::count();
        $this->agent_count              = Agent::count();
        $this->department_count         = Department::count();
        $this->trip_count               = Trip::whereYear('start_date',$year)->whereNull('deleted_at')->count();
        $this->horse_count              = Horse::where('archive',false)->count();
        $this->destinations_count       = Destination::count();
        $this->trailer_count            = Trailer::where('archive',false)->count();
        $this->vendor_count             = Vendor::where('status',true)->count();
        $this->vehicle_count            = Vehicle::where('archive',false)->count();
        $this->assignment_count         = Assignment::count();
        $this->inspection_count         = Checklist::whereYear('date',$year)->count();
        $this->branch_count             = Branch::count();
        $this->customer_count           = Customer::where('status',true)->count();
        $this->bill_count               = Bill::whereYear('bill_date',$year)->count();
        $this->invoice_count            = Invoice::whereYear('date',$year)->count();
        $this->employee_count           = Employee::doesntHave('driver')->where('archive',false)->count();
        $this->driver_count             = Driver::where('archive',false)->count();
        $this->tyre_count               = Tyre::where('disposed',0)->where('status',1)->count();
        $this->service_count            = Service::count();
        $this->inventory_count          = Inventory::where('disposed',0)->where('status',1)->count();
        $this->product_count            = Product::where('buy',true)->where('department','inventory')->where('status',1)->count();
        $this->inventory_dispatches_count = Dispatch::whereYear('date',$year)->count();
        $this->inventory_purchases_count  = Purchase::whereYear('date',$year)->where('department','inventory')->count();
        $this->booking_count            = Booking::where('authorization','approved')->whereYear('created_at',$year)->count();
        $this->ticket_count             = Ticket::whereYear('created_at',$year)->count();
        $this->workshop_inspection_count  = Inspection::whereYear('created_at',$year)->count();
        $this->fuel_supplier_count      = Container::count();
        $this->fuel_order_count         = Fuel::whereYear('date',$year)->count();
        $this->transport_order_count    = TransportOrder::whereYear('created_at',$year)->count();
        $this->leave_count              = Leave::whereYear('created_at',$year)->count();
        $this->attendance_count         = Attendance::whereMonth('created_at',date('m'))->count();

        $this->my_tickets_count = Ticket::whereHas('booking.employees', fn($q) => $q->where('employees.id',$this->employee->id))
            ->whereYear('created_at',$year)->count();
        $this->my_inspections_count = Inspection::whereHas('booking.employees', fn($q) => $q->where('employees.id',$this->employee->id))
            ->whereYear('created_at',$year)->count();

        if ($this->driver) {
            $did = $this->driver->id;
            $this->driver_inspections = Checklist::whereYear('date',$year)->whereNull('deleted_at')->where('driver_id',$did)->count();
            $this->driver_breakdowns  = Breakdown::whereYear('date',$year)->whereNull('deleted_at')->where('driver_id',$did)->count();
            $this->driver_trips       = Trip::whereYear('start_date',$year)->whereNull('deleted_at')->where('driver_id',$did)->count();
            $this->driver_recoveries  = Recovery::whereYear('date',$year)->whereNull('deleted_at')->where('driver_id',$did)->count();
        }
    }

    private function loadLists(): void
    {
        $this->transporters     = Transporter::latest()->take(5)->get();
        $this->agents           = Agent::latest()->take(5)->get();
        $this->bookings         = Booking::with(['horse.horse_make','horse.horse_model','vehicle.vehicle_make','vehicle.vehicle_model','trailer','service_type'])->latest()->take(5)->get();
        $this->fuel_orders      = Fuel::with(['horse.horse_make','horse.horse_model','trip','asset.product.brand','vehicle.vehicle_make','vehicle.vehicle_model','container'])->latest()->take(5)->get();
        $this->recent_employees = Employee::with('departments')->latest()->take(5)->get();
        $this->containers       = Container::latest()->get();
        $this->allocations      = Allocation::latest()->take(5)->get();
        $this->myallocations    = Allocation::where('employee_id',$this->employee->id)->latest()->take(5)->get();
        $this->hods             = DepartmentHead::with(['employee','department'])->get();

        $currencyId = $this->company_currency?->id ?? 2;

        $this->top_drivers = Driver::query()
            ->select(['drivers.id','drivers.employee_id',
                DB::raw('COUNT(trips.id) as trips_count'),
                DB::raw("COALESCE(SUM(CASE WHEN trips.currency_id={$currencyId} THEN NULLIF(trips.freight,'')+0 ELSE NULLIF(trips.exchange_customer_freight,'')+0 END),0) as total_revenue"),
            ])
            ->join('trips','drivers.id','=','trips.driver_id')
            ->whereYear('trips.start_date', now()->year)->whereMonth('trips.start_date', now()->month)
            ->where('trips.trip_status','Offloaded')->where('trips.authorization','approved')
            ->whereNull('trips.deleted_at')
            ->with(['employee:id,employee_number,name,surname'])
            ->groupBy('drivers.id','drivers.employee_id')->orderByDesc('trips_count')->limit(5)->get();

        $this->top_horses = Horse::query()
            ->select(['horses.id','horses.horse_number','horses.registration_number',
                'horses.fleet_number','horses.horse_make_id','horses.horse_model_id',
                DB::raw('COUNT(DISTINCT trips.id) as trips_count'),
                DB::raw("COALESCE(SUM(DISTINCT CASE WHEN fuels.id IS NOT NULL AND fuels.trip_id IS NOT NULL AND fuels.trip_id!='' AND fuels.quantity IS NOT NULL AND fuels.quantity!='' THEN fuels.quantity+0 ELSE 0 END),0) as fuel_usage"),
                DB::raw("COALESCE(SUM(CASE WHEN trips.currency_id={$currencyId} THEN NULLIF(trips.freight,'')+0 ELSE NULLIF(trips.exchange_customer_freight,'')+0 END),0) as total_revenue"),
            ])
            ->join('trips','horses.id','=','trips.horse_id')
            ->leftJoin('fuels', fn($j) => $j->on('fuels.horse_id','=','horses.id')
                ->whereMonth('fuels.date',now()->month)->whereYear('fuels.date',now()->year))
            ->whereYear('trips.start_date',now()->year)->whereMonth('trips.start_date',now()->month)
            ->where('trips.trip_status','Offloaded')->where('trips.authorization','approved')
            ->whereNull('trips.deleted_at')
            ->with(['horse_make:id,name','horse_model:id,name'])
            ->groupBy('horses.id','horses.horse_number','horses.registration_number','horses.fleet_number','horses.horse_make_id','horses.horse_model_id')
            ->orderByDesc('trips_count')->limit(5)->get();
    }

    private function loadChart(): void
    {
        $drivers = Driver::query()
            ->with(['employee:id,name,surname'])
            ->withSum(['trips as year_total_weight' => fn($q) => $q->whereYear('start_date',$this->year)], 'weight')
            ->orderByRaw('COALESCE(year_total_weight,0) DESC')
            ->get();
        $drivers_volume = Driver::query()
            ->with(['employee:id,name,surname'])
            ->withSum(['trips as year_total_volume' => fn($q) => $q->whereYear('start_date',$this->year)], 'litreage')
            ->orderByRaw('COALESCE(year_total_volume,0) DESC')
            ->get();

        $this->chartData = $drivers->map(function ($d) {
            $name = trim(($d->employee->name ?? '').' '.($d->employee->surname ?? ''));
            return [$name !== '' ? $name : 'Driver #'.$d->id, (float)($d->year_total_weight ?? 0)];
        })->values()->all();
       
        $this->chartVolumeData = $drivers_volume->map(function ($d) {
            $name = trim(($d->employee->name ?? '').' '.($d->employee->surname ?? ''));
            return [$name !== '' ? $name : 'Driver #'.$d->id, (float)($d->year_total_volume ?? 0)];
        })->values()->all();
    }

    private function loadFinancials(): void
    {
        $year = (int) date('Y');
        $cur  = $this->selectedCurrency;

        $inc = Invoice::whereYear('date',$year)->where('currency_id',$cur)->where('authorization','approved')
            ->selectRaw('MONTH(date) m, SUM(total+0) t')->groupBy('m')->get()->keyBy('m');
        $this->spread('%s', $this->pick($inc,'t'));

        $exp = Bill::whereYear('bill_date',$year)->where('currency_id',$cur)->where('to_be_paid',true)->where('authorization','approved')
            ->selectRaw('MONTH(bill_date) m, SUM(total+0) t')->groupBy('m')->get()->keyBy('m');
        $this->spread('%s_expense', $this->pick($exp,'t'));

        $yi = Invoice::where('currency_id',$cur)->where('authorization','approved')
            ->whereIn(DB::raw('YEAR(date)'),self::REVENUE_YEARS)
            ->selectRaw('YEAR(date) y, SUM(total+0) t')->groupBy('y')->pluck('t','y');
        $ye = Bill::where('currency_id',$cur)->where('to_be_paid',true)->where('authorization','approved')
            ->whereIn(DB::raw('YEAR(bill_date)'),self::REVENUE_YEARS)
            ->selectRaw('YEAR(bill_date) y, SUM(total+0) t')->groupBy('y')->pluck('t','y');

        foreach (self::REVENUE_YEARS as $y) {
            $this->{'income_'.$y}   = (float)($yi[$y] ?? 0);
            $this->{'expenses_'.$y} = (float)($ye[$y] ?? 0);
        }
    }

    public function formatCurrency(float $value, int $decimals = 0): string
    {
        if ($value >= 1_000_000) return number_format($value/1_000_000,1).'M';
        if ($value >= 1_000)     return number_format($value/1_000,1).'K';
        return number_format($value,$decimals);
    }

    public function render()
    {
        $this->currencies = Currency::orderBy('name','asc')->get();
        $this->loadFinancials();
        return view('livewire.dashboard.index');
    }
}
