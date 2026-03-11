<?php

namespace App\Exports;

use App\Models\Cargo;
use App\Models\Commission;
use App\Models\Consignee;
use App\Models\Currency;
use App\Models\Customer;
use App\Models\Destination;
use App\Models\Driver;
use App\Models\Horse;
use App\Models\InvoiceItem;
use App\Models\Route;
use App\Models\Transporter;
use App\Models\Trip;
use App\Models\TripDocument;
use App\Models\TripLocation;
use App\Models\TripType;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

class TripsReportExport implements FromQuery, ShouldAutoSize, WithMapping, WithHeadings, WithEvents, WithDrawings, WithCustomStartCell
{
    use Exportable;

    public $commission;
    public $from;
    public $to;
    public $trip_filter;
    public $search;

    public $department_names = [];
    public $role_names = [];
    public $user;
    public $employee;
    public $company;

    // Extra filters
    public $horse_id;
    public $currency_id;
    public $driver_id;
    public $customer_id;
    public $cargo_id;
    public $from_destination_id;
    public $to_destination_id;
    public $consignee_id;
    public $trip_type_id;
    public $transporter_id;
    public $route_id;
    public $trip_status;

    // Summary metrics
    protected int $totalTrips = 0;
    protected array $statusBreakdown = [];
    protected array $assetCounts = ['horse' => 0, 'vehicle' => 0, 'trailer' => 0];
    protected array $tripTypeBreakdown = [];
    protected ?int $avgTripMinutes = null;

    protected float $sumTurnover = 0.0;
    protected float $sumCogs = 0.0;
    protected float $sumNet = 0.0;

    protected float $totalRevenueBase = 0.0;
    protected float $totalExpensesBase = 0.0;
    protected float $totalNetProfitBase = 0.0;

    protected float $totalWeight = 0.0;
    protected float $totalVolume = 0.0;
    protected float $totalDistance = 0.0;

    protected ?float $volumePerKm = null;
    protected ?float $weightPerKm = null;

    protected ?float $weightLossRatio = null;
    protected ?float $volumeLossRatio = null;

    protected string $performanceRating = 'N/A';
    protected int $otifNumerator = 0;
    protected int $otifDenominator = 0;
    protected string $otifRatio = 'N/A';
    public $horse, $driver, $transporter, $trip_type, $customer, $route, $origin, $destination, $cargo, $consignee, $currency;

    public function __construct($from, $to, $trip_filter, $search, array $filters = [])
    {
        $this->from = $from;
        $this->to = $to;
        $this->trip_filter = $trip_filter;
        $this->search = $search;

        $this->horse_id            = $filters['horse_id'] ?? null;
        $this->horse = Horse::find($this->horse_id);
        $this->driver_id           = $filters['driver_id'] ?? null;
        $this->driver = Driver::find($this->driver_id);
        $this->customer_id         = $filters['customer_id'] ?? null;
        $this->customer = Customer::find($this->customer_id);
        $this->currency_id         = $filters['currency_id'] ?? null;
        $this->currency = Currency::find($this->currency_id);
        $this->cargo_id            = $filters['cargo_id'] ?? null;
        $this->cargo = Cargo::find($this->cargo_id);
        $this->from_destination_id = $filters['from_destination_id'] ?? null;
        $this->origin = Destination::find($this->from_destination_id);
        $this->to_destination_id   = $filters['to_destination_id'] ?? null;
        $this->destination = Destination::find($this->to_destination_id);
        $this->consignee_id      = $filters['consignee_id'] ?? null;
        $this->consignee = Consignee::find($this->consignee_id);
        $this->trip_type_id        = $filters['trip_type_id'] ?? null;
        $this->trip_type = TripType::find($this->trip_type_id);
        $this->transporter_id      = $filters['transporter_id'] ?? null;
        $this->transporter = Transporter::find($this->transporter_id);
        $this->route_id            = $filters['route_id'] ?? null;
        $this->route = Route::find($this->route_id);
        $this->trip_status  = $filters['trip_status'] ?? null;

        $this->user = Auth::user();
        $this->employee = $this->user->employee;
        $this->company = $this->employee->company;

        foreach (($this->employee->departments ?? []) as $department) {
            $this->department_names[] = $department->name;
        }

        foreach (($this->user->roles ?? []) as $role) {
            $this->role_names[] = $role->name;
        }

        $base = $this->query();

        // Financial metrics
        $money = (clone $base)->selectRaw("
            SUM(COALESCE(trips.turnover,0)) as total_revenue_base,
            SUM(COALESCE(trips.cost_of_sales,0)) as total_expenses_base
        ")->first();

        $this->totalRevenueBase   = (float) ($money->total_revenue_base ?? 0);
        $this->totalExpensesBase  = (float) ($money->total_expenses_base ?? 0);
        $this->totalNetProfitBase = $this->totalRevenueBase - $this->totalExpensesBase;

        // Totals
        $this->totalTrips = (clone $base)->count();

        // By Status
        $this->statusBreakdown = (clone $base)
            ->select('trips.trip_status', DB::raw('COUNT(*) as total'))
            ->groupBy('trips.trip_status')
            ->pluck('total', 'trips.trip_status')
            ->toArray();

        // By Asset Type
        $this->assetCounts['horse']   = (clone $base)->whereNotNull('trips.horse_id')->count();
        $this->assetCounts['vehicle'] = (clone $base)->whereNotNull('trips.vehicle_id')->count();
        $this->assetCounts['trailer'] = (clone $base)->whereHas('trailers')->count();

        // By Trip Type
        $this->tripTypeBreakdown = (clone $base)
            ->join('trip_types', 'trip_types.id', '=', 'trips.trip_type_id')
            ->groupBy('trip_types.name')
            ->orderByDesc(DB::raw('COUNT(*)'))
            ->limit(6)
            ->get(['trip_types.name as name', DB::raw('COUNT(*) as total')])
            ->toArray();

        // Average Trip Duration (Start -> Offloaded)
        $this->avgTripMinutes = (int) ((clone $base)
            ->whereNotNull('trips.start_date')
            ->whereHas('delivery_note', function ($dq) {
                $dq->whereNotNull('offloaded_date');
            })
            ->join('delivery_notes', 'delivery_notes.trip_id', '=', 'trips.id')
            ->whereRaw('TIMESTAMP(delivery_notes.offloaded_date) >= TIMESTAMP(trips.start_date)')
            ->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, TIMESTAMP(trips.start_date), TIMESTAMP(delivery_notes.offloaded_date))) AS avg_minutes')
            ->value('avg_minutes'));

       

        // Operational metrics
        $operational = (clone $base)
            ->leftJoin('delivery_notes', 'delivery_notes.trip_id', '=', 'trips.id')
            ->selectRaw("
                SUM(COALESCE(delivery_notes.loaded_weight, 0)) as total_weight,
                SUM(COALESCE(delivery_notes.loaded_litreage_at_20, 0)) as total_volume,
                SUM(
                    CASE
                        WHEN trips.starting_mileage IS NOT NULL
                         AND trips.ending_mileage IS NOT NULL
                         AND trips.ending_mileage >= trips.starting_mileage
                        THEN (trips.ending_mileage - trips.starting_mileage)
                        ELSE COALESCE(trips.distance, 0)
                    END
                ) as total_distance,
                SUM(COALESCE(delivery_notes.offloaded_weight, 0)) as total_offloaded_weight,
                SUM(COALESCE(delivery_notes.offloaded_litreage_at_20, 0)) as total_offloaded_volume,
                COUNT(delivery_notes.id) as otif_total,
                SUM(
                    CASE
                        WHEN delivery_notes.id IS NOT NULL
                        AND delivery_notes.offloaded_date IS NOT NULL
                        AND trips.end_date IS NOT NULL
                        AND TIMESTAMP(trips.end_date) >= TIMESTAMP(delivery_notes.offloaded_date)
                        AND (
                                (
                                    delivery_notes.loaded_weight IS NOT NULL
                                    AND delivery_notes.offloaded_weight IS NOT NULL
                                    AND ROUND(delivery_notes.loaded_weight, 2) = ROUND(delivery_notes.offloaded_weight, 2)
                                )
                                OR
                                (
                                    delivery_notes.loaded_litreage_at_20 IS NOT NULL
                                    AND delivery_notes.offloaded_litreage_at_20 IS NOT NULL
                                    AND ROUND(delivery_notes.loaded_litreage_at_20, 2) = ROUND(delivery_notes.offloaded_litreage_at_20, 2)
                                )
                            )
                        THEN 1
                        ELSE 0
                    END
                ) as otif_hits
            ")
            ->first();

        $this->totalWeight   = (float) ($operational->total_weight ?? 0);
        $this->totalVolume   = (float) ($operational->total_volume ?? 0);
        $this->totalDistance = (float) ($operational->total_distance ?? 0);

        $totalOffloadedWeight = (float) ($operational->total_offloaded_weight ?? 0);
        $totalOffloadedVolume = (float) ($operational->total_offloaded_volume ?? 0);

        if ($this->totalDistance > 0) {
            $this->volumePerKm = $this->totalVolume / $this->totalDistance;
            $this->weightPerKm = $this->totalWeight / $this->totalDistance;
        }

        if ($this->totalWeight > 0 && $totalOffloadedWeight > 0 ) {
            $this->weightLossRatio = (($this->totalWeight - $totalOffloadedWeight) / $this->totalWeight) * 100;
        }

        if ($this->totalVolume > 0 && $totalOffloadedVolume > 0) {
            $this->volumeLossRatio = (($this->totalVolume - $totalOffloadedVolume) / $this->totalVolume) * 100;
        }

        $this->otifNumerator   = (int) ($operational->otif_hits ?? 0);
        $this->otifDenominator = (int) ($operational->otif_total ?? 0);

        $this->otifRatio = $this->otifDenominator > 0
            ? $this->otifNumerator . '/' . $this->otifDenominator . ' (' . number_format(($this->otifNumerator / $this->otifDenominator) * 100, 2) . '%)'
            : 'N/A';

        $this->performanceRating = $this->buildPerformanceRating();
    }

    public function baseTripPeriodQuery()
    {
        $trips = Trip::query();

        if ($this->trip_filter === 'offloaded_date') {
            $trips->whereHas('delivery_note', function ($q) {
                if (filled($this->from) && filled($this->to)) {
                    $q->whereBetween('offloaded_date', [$this->from, $this->to]);
                } else {
                    $q->whereMonth('offloaded_date', date('m'))
                    ->whereYear('offloaded_date', date('Y'));
                }
            });
        } else {
            if (filled($this->from) && filled($this->to)) {
                $trips->whereBetween("trips.{$this->trip_filter}", [$this->from, $this->to]);
            } else {
                $trips->whereMonth("trips.{$this->trip_filter}", date('m'))
                    ->whereYear("trips.{$this->trip_filter}", date('Y'));
            }
        }

        return $trips;
    }

    public function baseTripQuery()
    {
        $withRelations = [
            'breakdowns',
            'breakdown_assignments',
            'trip_destinations',
            'trip_expenses',
            'trip_locations.country',
            'delivery_note',
            'fuels',
            'fuel:id,order_number',
            'transporter:id,name',
            'trip_type:id,name',
            'broker:id,name',
            'customer:id,name',
            'consignee:id,name',
            'horse',
            'horse.horse_make',
            'horse.horse_model',
            'vehicle',
            'vehicle.vehicle_make',
            'vehicle.vehicle_model',
            'trailers:id,make,model,registration_number,fleet_number',
            'driver.employee:id,name,surname',
            'user.employee:id,name,surname',
            'loading_point:id,name',
            'offloading_point:id,name',
            'route:id,name,rank',
            'truck_stops:id,name',
            'cargo:id,name,group,risk,type',
            'currency:id,name,symbol',
            'agent:id,name,surname',
            'commission:id,trip_id,agent_id,commission,amount',
            'trip_documents',
            'borders:id,name',
            'clearing_agents:id,name',
        ];

        $trips = $this->baseTripPeriodQuery()->with($withRelations);

        if (filled($this->horse_id)) {
            $trips->where('trips.horse_id', $this->horse_id);
        }

        if (filled($this->driver_id)) {
            $trips->where('trips.driver_id', $this->driver_id);
        }

        if (filled($this->currency_id)) {
            $trips->where('trips.currency_id', $this->currency_id);
        }

        if (filled($this->customer_id)) {
            $trips->where('trips.customer_id', $this->customer_id);
        }

        if (filled($this->cargo_id)) {
            $trips->where('trips.cargo_id', $this->cargo_id);
        }

        if (filled($this->from_destination_id)) {
            $trips->where('trips.from', $this->from_destination_id);
        }

        if (filled($this->to_destination_id)) {
            $trips->where('trips.to', $this->to_destination_id);
        }

        if (filled($this->consignee_id)) {
            $trips->where('trips.consignee_id', $this->consignee_id);
        }

        if (filled($this->trip_type_id)) {
            $trips->where('trips.trip_type_id', $this->trip_type_id);
        }

        if (filled($this->transporter_id)) {
            $trips->where('trips.transporter_id', $this->transporter_id);
        }

        if (filled($this->route_id)) {
            $trips->where('trips.route_id', $this->route_id);
        }

        if (filled($this->trip_status)) {
            $trips->where('trips.trip_status', $this->trip_status);
        }

        return $trips;
    }

    public function applyTripSearch($query)
    {
        $search = $this->search;

        $query->where(function ($q) use ($search) {
            $q->where('trip_number', 'like', "%{$search}%")
                ->orWhere('trip_status', 'like', "%{$search}%")
                ->orWhere('trip_ref', 'like', "%{$search}%")
                ->orWhere('authorization', 'like', "%{$search}%")
                ->orWhereHas('horse', function ($q2) use ($search) {
                    $q2->where('registration_number', 'like', "%{$search}%")
                    ->orWhere('fleet_number', 'like', "%{$search}%");
                })
                ->orWhereHas('trip_type', fn ($q2) => $q2->where('name', 'like', "%{$search}%"))
                ->orWhereHas('customer', fn ($q2) => $q2->where('name', 'like', "%{$search}%"))
                ->orWhereHas('cargo', fn ($q2) => $q2->where('name', 'like', "%{$search}%"))
                ->orWhereRaw("DATE_FORMAT(start_date, '%Y-%m-%d') LIKE ?", ["%{$search}%"])
                ->orWhereRaw("DATE_FORMAT(end_date, '%Y-%m-%d') LIKE ?", ["%{$search}%"])
                ->orWhereHas('delivery_note', fn ($q2) =>
                    $q2->whereRaw("DATE_FORMAT(offloaded_date, '%Y-%m-%d') LIKE ?", ["%{$search}%"])
                )
                ->orWhereHas('user.employee', fn ($q2) =>
                    $q2->where(DB::raw("concat(name, ' ', surname)"), 'like', "%{$search}%")
                )
                ->orWhereHas('driver.employee', fn ($q2) =>
                    $q2->where(DB::raw("concat(name, ' ', surname)"), 'like', "%{$search}%")
                )
                ->orWhereHas('transporter', fn ($q2) => $q2->where('name', 'like', "%{$search}%"))
                ->orWhereHas('vehicle', function ($q2) use ($search) {
                    $q2->where('registration_number', 'like', "%{$search}%")
                    ->orWhere('fleet_number', 'like', "%{$search}%");
                })
                ->orWhereHas('trailers', function ($q2) use ($search) {
                    $q2->where('registration_number', 'like', "%{$search}%")
                    ->orWhere('fleet_number', 'like', "%{$search}%");
                })
                ->orWhereHas('loading_point', fn ($q2) => $q2->where('name', 'like', "%{$search}%"))
                ->orWhereHas('offloading_point', fn ($q2) => $q2->where('name', 'like', "%{$search}%"))
                ->orWhereHas('trip_documents', fn ($q2) => $q2->where('document_number', 'like', "%{$search}%"));
        });

        return $query;
    }

    public function query()
    {
        $trips = $this->baseTripQuery();

        if (filled($this->search)) {
            $this->applyTripSearch($trips);
        }

        if ($this->trip_filter === 'offloaded_date') {
            $trips->join('delivery_notes', 'delivery_notes.trip_id', '=', 'trips.id')
                ->select('trips.*')
                ->orderBy('delivery_notes.offloaded_date', 'desc');
        } else {
            $trips->orderBy("trips.{$this->trip_filter}", 'desc');
        }

        return $trips;
    }

    public function map($trip): array
    {
        $trailers = [];
        $truck_stops = [];
        $borders = [];
        $clearing_agents = [];

        if ($trip->horse) {
            $fleet_number = $trip->horse->fleet_number ? '(' . $trip->horse->fleet_number . ')' : "";
            $horse_registration_number = $trip->horse->registration_number ?? "";
            $horse_full_details = trim($horse_registration_number . ' ' . $fleet_number);
        } else {
            $horse_full_details = "";
        }

        foreach (($trip->trailers ?? []) as $trailer) {
            $fleet_number = $trailer->fleet_number ? '(' . $trailer->fleet_number . ')' : "";
            $trailers[] = trim(($trailer->registration_number ?? '') . ' ' . $fleet_number);
        }
        $trailer_list = !empty($trailers) ? implode(', ', $trailers) : "";

        if (($trip->truck_stops ?? collect())->count() > 0) {
            foreach ($trip->truck_stops as $truck_stop) {
                $truck_stops[] = $truck_stop->name;
            }
            $truck_stop_list = implode(',', $truck_stops);
        } else {
            $truck_stop_list = "";
        }

        $from_destination = $trip->from ? Destination::find($trip->from) : null;
        $to_destination = $trip->to ? Destination::find($trip->to) : null;

        if ($from_destination) {
            $from_country = $from_destination->country ? $from_destination->country->name : "";
            $from_city = $from_destination->city ?? "";
        } else {
            $from_country = "";
            $from_city = "";
        }

        if ($to_destination) {
            $to_country = $to_destination->country ? $to_destination->country->name : "";
            $to_city = $to_destination->city ?? "";
        } else {
            $to_country = "";
            $to_city = "";
        }

        if ($trip->driver) {
            $driver_name = $trip->driver->employee->name ?? "";
            $driver_surname = $trip->driver->employee->surname ?? "";
        } else {
            $driver_name = "";
            $driver_surname = "";
        }

        if (($trip->borders ?? collect())->count() > 0) {
            foreach ($trip->borders as $border) {
                $borders[] = $border->name;
            }
            $border_list = implode(',', $borders);
        } else {
            $border_list = "";
        }

        if (($trip->clearing_agents ?? collect())->count() > 0) {
            foreach ($trip->clearing_agents as $clearing_agent) {
                $clearing_agents[] = $clearing_agent->name;
            }
            $clearing_agent_list = implode(',', $clearing_agents);
        } else {
            $clearing_agent_list = "";
        }

        $agent = $trip->agent;
        $agent_name = $trip->agent->name ?? "";
        $agent_surname = $trip->agent->surname ?? "";

        $this->commission = null;
        if ($agent) {
            $this->commission = Commission::where('trip_id', $trip->id)
                ->where('agent_id', $agent->id)
                ->first();
        }

        if ($this->commission) {
            $commission_percentage = $this->commission->commission ?? "";
            $commission_amount = $this->commission->amount ?? "";
        } else {
            $commission_percentage = "";
            $commission_amount = "";
        }

        $symbol = $trip->currency->symbol ?? "";

        $latest_location = TripLocation::where('trip_id', $trip->id)->latest()->first();
        if ($latest_location) {
            $location = ($latest_location->country ? $latest_location->country->name : "") . ' | ' . ($latest_location->description ?? "");
        } else {
            $location = "";
        }

        $total_transporter_expenses = 0;
        $total_customer_expenses = 0;
        $total_expenses = 0;

        foreach (($trip->trip_expenses ?? []) as $expense) {
            $amountToUse = ($expense->currency_id == Auth::user()->employee->company->currency_id)
                ? $expense->amount
                : $expense->exchange_amount;

            if (!is_numeric($amountToUse)) {
                continue;
            }

            if ($expense->category == "Transporter" || $expense->category == "transporter") {
                $total_transporter_expenses += $amountToUse;
            } elseif ($expense->category == "Customer" || $expense->category == "customer") {
                $total_customer_expenses += $amountToUse;
            } elseif ($expense->category == "Self" || $expense->category == "self") {
                $total_expenses += $amountToUse;
            }
        }

        if (isset($trip->cost_of_sales) && $trip->cost_of_sales !== "" && $trip->cost_of_sales > 0 && is_numeric($trip->cost_of_sales)) {
            if (isset($trip->turnover) && $trip->turnover !== "" && $trip->turnover > 0 && is_numeric($trip->turnover)) {
                $net_profit = $trip->turnover - $trip->cost_of_sales;

                if (isset($net_profit) && is_numeric($net_profit) && $trip->turnover > 0 && $trip->cost_of_sales > 0) {
                    $markup_percentage = ($net_profit / $trip->cost_of_sales) * 100;
                    $net_profit_percentage = ($net_profit / $trip->turnover) * 100;
                } else {
                    $net_profit_percentage = 100;
                    $markup_percentage = 100;
                }
            } else {
                $net_profit = $trip->turnover;
                $net_profit_percentage = 100;
                $markup_percentage = 100;
            }
        } else {
            $net_profit = $trip->turnover;
            $net_profit_percentage = 100;
            $markup_percentage = 100;
        }

        if (isset($trip->starting_mileage) && is_numeric($trip->starting_mileage) && isset($trip->ending_mileage) && is_numeric($trip->ending_mileage)) {
            $actual_distance = $trip->ending_mileage - $trip->starting_mileage;
        } else {
            $actual_distance = $trip->distance ?? "";
        }

        $pod = TripDocument::where('trip_id', $trip->id)->where('title', 'POD')->first();
        $invoice_item = InvoiceItem::where('trip_id', $trip->id)->first();

        if ($invoice_item) {
            $invoice_number = $invoice_item->invoice ? $invoice_item->invoice->invoice_number : "";
            $invoice_date = $invoice_item->invoice ? $invoice_item->invoice->date : "";
        } else {
            $invoice_number = "";
            $invoice_date = "";
        }

        $start_date = $this->formatDateTimeValue($trip->start_date ?? null);
        $loading_date = $trip->delivery_note ? $this->formatDateTimeValue($trip->delivery_note->loaded_date ?? null) : "";
        $offloading_date = $trip->delivery_note ? $this->formatDateTimeValue($trip->delivery_note->offloaded_date ?? null) : "";

        $fuel_purchased = ($trip->fuels ?? collect())->where('amount', '!=', null)->where('amount', '!=', '')->sum('amount');
        $fuel_sold = ($trip->fuels ?? collect())->where('transporter_total', '!=', null)->where('transporter_total', '!=', '')->sum('transporter_total');
        $fuel_profit = ($trip->fuels ?? collect())->where('profit', '!=', null)->where('profit', '!=', '')->sum('profit');

        $weight = $trip->weight ?: "";
        $quantity = $trip->quantity ?: "";
        $litreage = $trip->litreage ?: "";
        $litreage_at_20 = $trip->litreage_at_20 ?: "";

        if ($trip->delivery_note) {
            $loaded_weight = $trip->delivery_note->loaded_weight ?: "";
            $loaded_quantity = $trip->delivery_note->loaded_quantity ?: "";
            $loaded_litreage = $trip->delivery_note->loaded_litreage ?: "";
            $loaded_litreage_at_20 = $trip->delivery_note->loaded_litreage_at_20 ?: "";

            $offloaded_weight = $trip->delivery_note->offloaded_weight ?: "";
            $offloaded_quantity = $trip->delivery_note->offloaded_quantity ?: "";
            $offloaded_litreage = $trip->delivery_note->offloaded_litreage ?: "";
            $offloaded_litreage_at_20 = $trip->delivery_note->offloaded_litreage_at_20 ?: "";
        } else {
            $loaded_weight = "";
            $loaded_quantity = "";
            $loaded_litreage = "";
            $loaded_litreage_at_20 = "";
            $offloaded_weight = "";
            $offloaded_quantity = "";
            $offloaded_litreage = "";
            $offloaded_litreage_at_20 = "";
        }

        $weight_loss = (
            isset($trip->delivery_note->loaded_weight) &&
            is_numeric($trip->delivery_note->loaded_weight) &&
            isset($trip->delivery_note->offloaded_weight) &&
            is_numeric($trip->delivery_note->offloaded_weight)
        ) ? ($trip->delivery_note->loaded_weight - $trip->delivery_note->offloaded_weight) : "";

        $quantity_loss = (
            isset($trip->delivery_note->loaded_quantity) &&
            is_numeric($trip->delivery_note->loaded_quantity) &&
            isset($trip->delivery_note->offloaded_quantity) &&
            is_numeric($trip->delivery_note->offloaded_quantity)
        ) ? ($trip->delivery_note->loaded_quantity - $trip->delivery_note->offloaded_quantity) : "";

        $litreage_at_20_loss = (
            isset($trip->delivery_note->loaded_litreage_at_20) &&
            is_numeric($trip->delivery_note->loaded_litreage_at_20) &&
            isset($trip->delivery_note->offloaded_litreage_at_20) &&
            is_numeric($trip->delivery_note->offloaded_litreage_at_20)
        ) ? ($trip->delivery_note->loaded_litreage_at_20 - $trip->delivery_note->offloaded_litreage_at_20) : "";

        if (isset($litreage_at_20_loss) && is_numeric($litreage_at_20_loss) && $litreage_at_20_loss > 0 && isset($trip->allowable_loss_litreage) && is_numeric($trip->allowable_loss_litreage) && $trip->allowable_loss_litreage > 0) {
            $chargeable_litreage_loss = $litreage_at_20_loss - $trip->allowable_loss_litreage;

            $deductable_litreage_loss = (isset($trip->rate) && is_numeric($trip->rate) && is_numeric($chargeable_litreage_loss))
                ? $trip->rate * $chargeable_litreage_loss
                : "";

            $transporter_deductable_litreage_loss = (isset($trip->transporter_rate) && is_numeric($trip->transporter_rate) && is_numeric($chargeable_litreage_loss))
                ? $trip->transporter_rate * $chargeable_litreage_loss
                : "";
        } else {
            $chargeable_litreage_loss = "";
            $transporter_deductable_litreage_loss = "";
            $deductable_litreage_loss = "";
        }

        if (isset($quantity_loss) && is_numeric($quantity_loss) && $quantity_loss > 0 && isset($trip->allowable_loss_quantity) && is_numeric($trip->allowable_loss_quantity) && $trip->allowable_loss_quantity > 0) {
            $chargeable_quantity_loss = $quantity_loss - $trip->allowable_loss_quantity;
        } else {
            $chargeable_quantity_loss = "";
        }

        if (isset($weight_loss) && is_numeric($weight_loss) && $weight_loss > 0 && isset($trip->allowable_loss_weight) && is_numeric($trip->allowable_loss_weight) && $trip->allowable_loss_weight > 0) {
            $chargeable_weight_loss = $weight_loss - $trip->allowable_loss_weight;

            $deductable_weight_loss = (isset($trip->rate) && is_numeric($trip->rate) && is_numeric($chargeable_weight_loss))
                ? $trip->rate * $chargeable_weight_loss
                : "";

            $transporter_deductable_weight_loss = (isset($trip->transporter_rate) && is_numeric($trip->transporter_rate) && is_numeric($chargeable_weight_loss))
                ? $trip->transporter_rate * $chargeable_weight_loss
                : "";
        } else {
            $chargeable_weight_loss = "";
            $deductable_weight_loss = "";
            $transporter_deductable_weight_loss = "";
        }

        $cost_of_sales_less_transporter_expenses = 0;

        if ($trip->currency_id == Auth::user()->employee->company->currency_id) {
            if ($total_transporter_expenses > 0 && is_numeric($trip->transporter_freight) && is_numeric($total_transporter_expenses)) {
                $cost_of_sales_less_transporter_expenses = $trip->transporter_freight - $total_transporter_expenses;
            }
        } else {
            if ($total_transporter_expenses > 0 && is_numeric($trip->exchange_transporter_freight) && is_numeric($total_transporter_expenses)) {
                $cost_of_sales_less_transporter_expenses = $trip->exchange_transporter_freight - $total_transporter_expenses;
            }
        }

        return [
            $trip->trip_number . ($trip->trip_ref ? " / " . $trip->trip_ref : ""),
            $start_date,
            $loading_date,
            $offloading_date,
            $trip->trip_type ? $trip->trip_type->name : "",
            $border_list,
            $clearing_agent_list,
            $trip->transporter ? $trip->transporter->name : "",
            $horse_full_details,
            $trailer_list,
            trim($driver_name . ' ' . $driver_surname),
            $trip->customer ? $trip->customer->name : "",
            $trip->consignee ? $trip->consignee->name : "",
            trim($agent_name . ' ' . $agent_surname),
            $commission_percentage,
            $symbol . number_format($commission_amount ? $commission_amount : 0, 2),
            $trip->broker ? $trip->broker->name : "",
            trim($from_country . ' ' . $from_city),
            trim($to_country . ' ' . $to_city),
            $trip->loading_point ? $trip->loading_point->name : "",
            $trip->offloading_point ? $trip->offloading_point->name : "",
            $trip->route ? $trip->route->name : "",
            $truck_stop_list,
            $trip->trip_fuel ? $trip->trip_fuel . ' L' : "",
            $trip->starting_mileage ?: "",
            $trip->ending_mileage ?: "",
            $actual_distance,
            $trip->cargo ? $trip->cargo->name : "",
            $weight,
            $loaded_weight,
            $offloaded_weight,
            $quantity ? $quantity . ' ' . $trip->measurement : "",
            $loaded_quantity ? $loaded_quantity . ' ' . $trip->measurement : "",
            $offloaded_quantity ? $offloaded_quantity . ' ' . $trip->measurement : "",
            $litreage_at_20 ?: "",
            $loaded_litreage_at_20 ?: "",
            $offloaded_litreage_at_20 ?: "",
            $weight_loss ?: "",
            $quantity_loss ?: "",
            $litreage_at_20_loss ?: "",
            $trip->allowable_loss_weight ?: "",
            $trip->allowable_loss_quantity ?: "",
            $trip->allowable_loss_litreage ?: "",
            $chargeable_weight_loss ?: "",
            $chargeable_quantity_loss ?: "",
            $chargeable_litreage_loss ?: "",
            $trip->currency ? $trip->currency->name : "",
            $trip->rate ? number_format($trip->rate, 2) : 0,
            $trip->freight ? number_format($trip->freight, 2) : 0,
            $total_customer_expenses ? number_format($total_customer_expenses, 2) : 0,
            $trip->transporter_rate ? number_format($trip->transporter_rate, 2) : 0,
            $trip->transporter_freight ? number_format($trip->transporter_freight, 2) : 0,
            $total_transporter_expenses ? number_format($total_transporter_expenses, 2) : 0,
            $trip->turnover ? number_format($trip->turnover, 2) : 0,
            $trip->gross_profit ? number_format($trip->gross_profit, 2) : 0,
            $trip->transporter_freight ? number_format($trip->transporter_freight, 2) : 0,
            $total_expenses ? number_format($total_expenses, 2) : 0,
            $deductable_weight_loss ? number_format($deductable_weight_loss, 2) : 0,
            $transporter_deductable_weight_loss ? number_format($transporter_deductable_weight_loss, 2) : 0,
            $deductable_litreage_loss ? number_format($deductable_litreage_loss, 2) : 0,
            $transporter_deductable_litreage_loss ? number_format($transporter_deductable_litreage_loss, 2) : 0,
            $trip->cost_of_sales ? number_format($trip->cost_of_sales, 2) : 0,
            $net_profit ? number_format($net_profit, 2) : 0,
            $cost_of_sales_less_transporter_expenses ? number_format($cost_of_sales_less_transporter_expenses, 2) : 0,
            isset($net_profit_percentage) ? number_format($net_profit_percentage, 2) . '%' : 0,
            isset($markup_percentage) ? number_format($markup_percentage, 2) . '%' : 0,
            $fuel_purchased ?: "",
            $fuel_sold ?: "",
            $fuel_profit ?: "",
            $trip->trip_status,
            $trip->trip_status_date,
            $location,
            isset($pod) ? "Submitted" : "Pending",
            $invoice_date,
            $invoice_number,
            $trip->comments,
        ];
    }

    public function headings(): array
    {
        return [
            'Trip#/Ref',
            'Date Booked',
            'Date Loaded',
            'Date Offloaded',
            'Trip Type',
            'Border',
            'Clearing Agent',
            'Transporter',
            'Horse',
            'Trailer(s)',
            'Driver',
            'Customer',
            'Consignee',
            'Agent',
            'Commision %',
            'Commission Amount',
            'Broker',
            'From',
            'To',
            'Loading Point',
            'Offloading Point',
            'Route',
            'Truck Stop(s)',
            'Approximate Fuel',
            'Starting Mileage',
            'Ending Mileage',
            'Distance',
            'Cargo',
            'Scheduled Weight (Tons)',
            'Loaded Weight (Tons)',
            'Offloaded Weight (Tons)',
            'Scheduled Quantity',
            'Loaded Quantity',
            'Offloaded Quantity',
            'Scheduled Vol (Litre)',
            'Loaded Vol (Litre)',
            'Offloaded Vol (Litre)',
            'Transit Loss (Tons)',
            'Transit Qty Loss',
            'Transit Loss (Litres)',
            'Allowable Loss (Tons)',
            'Allowable Qty Loss',
            'Allowable Loss (Litres)',
            'Chargeable Loss (Tons)',
            'Chargeable Qty Loss',
            'Chargeable Loss (Litres)',
            'Currency',
            'Rate',
            'Freight',
            'Client Expenses',
            'Trans Rate',
            'Trans Freight',
            'Trans Expenses',
            'TurnOver',
            'Gross Profit',
            'Trans Gross Profit',
            'Expenses',
            'Deductable Weight Loss Val',
            'Trans Deductable Weight Loss Val',
            'Deductable Vol Loss Val',
            'Trans Deductable Vol Loss Val',
            'Cost Of Sales',
            'Net Profit',
            'Trans Net Profit',
            'Net Profit Percentage',
            'Markup Percentage',
            'Fuel Purchased',
            'Fuel Sold',
            'Fuel Profit',
            'Trip Status',
            'Updated On',
            'Location',
            'POD Status',
            'Date Invoiced',
            'Invoice#',
            'Comments',
        ];
    }

   public function registerEvents(): array
    {
        $statusLine = 'None';
        if (!empty($this->statusBreakdown)) {
            $pairs = [];
            foreach ($this->statusBreakdown as $status => $count) {
                $pairs[] = ($status ?? 'Unknown') . ' ' . (int) $count;
            }
            $statusLine = implode(', ', $pairs);
        }

        $tripTypeLine = 'None';
        if (!empty($this->tripTypeBreakdown)) {
            $pairs = array_map(fn($t) => ($t['name'] ?? 'Unknown') . ' ' . ($t['total'] ?? 0), $this->tripTypeBreakdown);
            $tripTypeLine = implode(', ', $pairs);
        }

        $avgTrip = $this->avgTripMinutes ? $this->formatMinutes($this->avgTripMinutes) : 'N/A';

        return [
            AfterSheet::class => function (AfterSheet $event) use ($statusLine, $tripTypeLine, $avgTrip) {
                $sheet = $event->sheet->getDelegate();

                $sheet->getStyle('A21:BX21')->applyFromArray([
                    'font' => ['bold' => true],
                    'borders' => [
                        'outline' => [
                            'borderStyle' => Border::BORDER_THICK,
                            'color' => ['argb' => 'FFFF0000'],
                        ],
                    ],
                ]);

                /*
                |--------------------------------------------------------------------------
                | Report meta line on Row 1
                |--------------------------------------------------------------------------
                */
                $filters = [];

                if (!empty($this->search)) {
                    $filters[] = 'Search: ' . $this->search;
                }

                if (!empty($this->from) && !empty($this->to)) {
                    $filters[] = 'Date Range: ' . $this->from . ' to ' . $this->to;
                } elseif (!empty($this->from)) {
                    $filters[] = 'From: ' . $this->from;
                } elseif (!empty($this->to)) {
                    $filters[] = 'To: ' . $this->to;
                }

                if (!empty($this->trip_type)) {
                    $filters[] = 'Trip Type: ' . (is_object($this->trip_type) && isset($this->trip_type->name)
                        ? $this->trip_type->name
                        : $this->trip_type);
                }

                if (!empty($this->customer)) {
                    $filters[] = 'Customer: ' . (is_object($this->customer) && isset($this->customer->name)
                        ? $this->customer->name
                        : $this->customer);
                }
                if (!empty($this->cargo)) {
                    $filters[] = 'Cargo: ' . (is_object($this->cargo) && isset($this->cargo->name)
                        ? $this->cargo->name
                        : $this->cargo);
                }

                if (!empty($this->transporter)) {
                    $filters[] = 'Transporter: ' . (is_object($this->transporter) && isset($this->transporter->name)
                        ? $this->transporter->name
                        : $this->transporter);
                }

                if (!empty($this->driver)) {
                    $filters[] = 'Driver: ' . (is_object($this->driver) && isset($this->driver->employee)
                        ? $this->driver->employee?->name ." ".$this->driver->employee?->surname
                        : $this->driver);
                }

                if (!empty($this->horse)) {
                    $filters[] = 'Horse: ' . (is_object($this->horse) && isset($this->horse->registration_number)
                        ? $this->horse->registration_number ." (".$this->horse->fleet_number.")"
                        : $this->horse);
                }


                if (!empty($this->origin)) {
                    $filters[] = 'Origin: ' . (is_object($this->origin) && isset($this->origin->country)
                        ? $this->origin->country ." ".$this->origin->city
                        : $this->origin);
                }

                if (!empty($this->destination)) {
                    $filters[] = 'Destination: ' . (is_object($this->destination) && isset($this->destination->country)
                        ? $this->destination->country ." ".$this->destination->city
                        : $this->destination);
                }

              
                if (!empty($this->route)) {
                    $filters[] = 'Route: ' . (is_object($this->route) && isset($this->route->name)
                        ? $this->route->name
                        : $this->route);
                }

                if (!empty($this->trip_status)) {
                    $filters[] = 'Status: ' . $this->trip_status;
                }

                if (!empty($this->currency)) {
                    $filters[] = 'Currency: ' . (is_object($this->currency) && isset($this->currency->name)
                        ? $this->currency->name
                        : $this->currency);
                }

                $filtersText = !empty($filters) ? implode(' | ', $filters) : '';

                $generatedText = 'This report was generated on ' . now()->format('d M Y H:i') . ' by ' . Auth::user()->name ." ".Auth::user()->surname;

                if ($filtersText !== '') {
                    $generatedText .= '. Applied filters: ' . $filtersText . '.';
                } else {
                    $generatedText .= '.';
                }

                $sheet->mergeCells('A1:BX1');
                $sheet->setCellValue('A1', $generatedText);
                $sheet->getStyle('A1:BX1')->applyFromArray([
                    'font' => [
                        'italic' => true,
                        'size' => 10,
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_LEFT,
                        'vertical' => Alignment::VERTICAL_CENTER,
                        'wrapText' => true,
                    ],
                ]);

                /*
                |--------------------------------------------------------------------------
                | Report summary heading on Row 2
                |--------------------------------------------------------------------------
                */
                $sheet->mergeCells('C3:D3');
                $sheet->setCellValue('C3', 'REPORT SUMMARY');
                $sheet->getStyle('C3:D3')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 12,
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                   
                ]);

                $row = 4;

                $baseSymbol = optional($this->company->currency)->symbol ?? '';

                $sheet->setCellValue("C{$row}", 'Total Trips');
                $sheet->setCellValue("D{$row}", $this->totalTrips);
                $row++;

                if ($this->company->rates_managed_by_finance == true) {
                    if (in_array('Finance', $this->department_names) || in_array('Super Admin', $this->role_names)) {
                        $sheet->setCellValue("C{$row}", 'Total Revenue');
                        $sheet->setCellValue("D{$row}", $baseSymbol . number_format($this->totalRevenueBase, 2));
                        $row++;

                        $sheet->setCellValue("C{$row}", 'Total Expenses');
                        $sheet->setCellValue("D{$row}", $baseSymbol . number_format($this->totalExpensesBase, 2));
                        $row++;

                        $sheet->setCellValue("C{$row}", 'Net Profit');
                        $sheet->setCellValue("D{$row}", $baseSymbol . number_format($this->totalNetProfitBase, 2));
                        $row++;
                    }
                } else {
                    $sheet->setCellValue("C{$row}", 'Total Revenue');
                    $sheet->setCellValue("D{$row}", $baseSymbol . number_format($this->totalRevenueBase, 2));
                    $row++;

                    $sheet->setCellValue("C{$row}", 'Total Expenses');
                    $sheet->setCellValue("D{$row}", $baseSymbol . number_format($this->totalExpensesBase, 2));
                    $row++;

                    $sheet->setCellValue("C{$row}", 'Net Profit');
                    $sheet->setCellValue("D{$row}", $baseSymbol . number_format($this->totalNetProfitBase, 2));
                    $row++;
                }

                $sheet->setCellValue("C{$row}", 'By Status');
                $sheet->setCellValue("D{$row}", $statusLine);
                $row++;

                $sheet->setCellValue("C{$row}", 'Avg Trip Duration');
                $sheet->setCellValue("D{$row}", $avgTrip);
                $row++;

                $sheet->setCellValue("C{$row}", 'Total Weight');
                $sheet->setCellValue("D{$row}", number_format($this->totalWeight, 2) . ' Tons');
                $row++;

                $sheet->setCellValue("C{$row}", 'Total Volume');
                $sheet->setCellValue("D{$row}", number_format($this->totalVolume, 2) . ' Litres');
                $row++;

                $sheet->setCellValue("C{$row}", 'Total Distance');
                $sheet->setCellValue("D{$row}", number_format($this->totalDistance, 2) . ' Km');
                $row++;

                $sheet->setCellValue("C{$row}", 'Productivity Ratio');
                $sheet->setCellValue("D{$row}", $this->formatRatio($this->volumePerKm, $this->weightPerKm));
                $row++;

                $sheet->setCellValue("C{$row}", 'Weight Loss Ratio');
                $sheet->setCellValue("D{$row}", $this->formatPercentage($this->weightLossRatio));
                $row++;

                $sheet->setCellValue("C{$row}", 'Volume Loss Ratio');
                $sheet->setCellValue("D{$row}", $this->formatPercentage($this->volumeLossRatio));
                $row++;

                $sheet->setCellValue("C{$row}", 'Performance Rating');
                $sheet->setCellValue("D{$row}", $this->performanceRating);
                $row++;

                $sheet->setCellValue("C{$row}", 'OTIF Ratio');
                $sheet->setCellValue("D{$row}", $this->otifRatio);
                $row++;

                $sheet->setCellValue("C{$row}", 'By Asset Type');
                $sheet->setCellValue(
                    "D{$row}",
                    sprintf(
                        'Horse %d, Vehicle %d, Trailer %d',
                        $this->assetCounts['horse'] ?? 0,
                        $this->assetCounts['vehicle'] ?? 0,
                        $this->assetCounts['trailer'] ?? 0
                    )
                );
                $row++;

                $sheet->setCellValue("C{$row}", 'By Trip Type');
                $sheet->setCellValue("D{$row}", $tripTypeLine);
                $row++;

                $final_row = $row - 1;

                $sheet->getStyle("C4:D{$final_row}")->applyFromArray([
                    'font' => ['bold' => true],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'FCE4D6'],
                    ],
                ]);

                $sheet->getStyle("D4:D{$final_row}")
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_LEFT);
            },
        ];
    }

    protected function buildPerformanceRating(): string
    {
        $baseTrips = $this->baseTripPeriodQuery();

        if (filled($this->driver_id)) {
          $activeDriversCount = (clone $baseTrips)
            ->whereNotNull('driver_id')
            ->distinct('driver_id')
            ->count('driver_id');

            return '1/' . max($activeDriversCount, 1);
        }

        if (filled($this->horse_id)) {
           $activeHorsesCount = (clone $baseTrips)
            ->whereNotNull('horse_id')
            ->distinct('horse_id')
            ->count('horse_id');

            return '1/' . max($activeHorsesCount, 1);
        }

        return 'N/A';
    }

    protected function formatMinutes(int $minutes): string
    {
        if ($minutes <= 0) {
            return '0m';
        }

        $days = intdiv($minutes, 1440);
        $minutes -= $days * 1440;

        $hours = intdiv($minutes, 60);
        $minutes -= $hours * 60;

        $parts = [];
        if ($days) {
            $parts[] = "{$days}d";
        }
        if ($hours) {
            $parts[] = "{$hours}h";
        }
        if ($minutes) {
            $parts[] = "{$minutes}m";
        }

        return implode(' ', $parts);
    }

    protected function formatRatio(?float $volumePerKm, ?float $weightPerKm): string
    {
        if (($volumePerKm === null || $volumePerKm <= 0) && ($weightPerKm === null || $weightPerKm <= 0)) {
            return 'N/A';
        }

        $parts = [];

        if ($volumePerKm !== null && $volumePerKm > 0) {
            $parts[] = number_format($volumePerKm, 2) . ' L/km';
        }

        if ($weightPerKm !== null && $weightPerKm > 0) {
            $parts[] = number_format($weightPerKm, 2) . ' t/km';
        }

        return implode(' | ', $parts);
    }

    protected function formatPercentage(?float $value): string
    {
        return $value !== null ? number_format($value, 2) . '%' : 'N/A';
    }

    protected function formatDateTimeValue($value): string
    {
        if (!$value) {
            return '';
        }

        try {
            return Carbon::parse($value)->format('d M Y g:i A');
        } catch (\Throwable $e) {
            return (string) $value;
        }
    }

    public function drawings()
    {
        $drawing = new Drawing();

        if (isset(Auth::user()->employee->company)) {
            $drawing->setName(Auth::user()->employee->company->name);
            $drawing->setDescription(Auth::user()->employee->company->name . ' Logo');

            if (file_exists(public_path('/images/uploads/' . Auth::user()->employee->company->logo))) {
                $drawing->setPath(public_path('/images/uploads/' . Auth::user()->employee->company->logo));
            } else {
                $drawing->setPath(public_path('/images/uploads/logo.png'));
            }
        }

        $drawing->setHeight(90);
        $drawing->setCoordinates('A3');

        return $drawing;
    }

    public function startCell(): string
    {
        return 'A21';
    }
}