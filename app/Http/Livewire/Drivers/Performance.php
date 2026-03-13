<?php

namespace App\Http\Livewire\Drivers;

use Carbon\Carbon;
use App\Models\Trip;
use App\Models\Shift;
use App\Models\Driver;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Excel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Exports\DriversPerformanceExport;

class Performance extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    private $drivers;
    public $filter;
    public $from;
    public $to;
    public $totalFuel;
    public $currency;
     public $year;
    public $chartData;

    public function mount(){
        $this->filter = "created_at";
        $this->currency = Auth::user()->employee->company->currency;  
        // $this->drivers = Driver::orderBy('registration_number','asc')->get();
        $this->year = now()->year;
        $this->loadChart();
       
    }


    public function updatedYear()
    {
        $this->loadChart();

        // Push updated data to the browser (no full page refresh needed)
        $this->emit('drivers-weight-updated', [
            'data' => $this->chartData,
            'year' => $this->year,
        ]);
    }

    private function loadChart(): void
    {
        $drivers = Driver::query()
            ->with(['employee:id,name,surname']) // adjust if your columns differ
            ->withSum([
                'trips as year_total_weight' => function ($q) {
                    $q->whereYear('start_date', $this->year);   // <-- your trip date column
                }
            ], 'weight')                                  // <-- your weight column
            ->orderByRaw('COALESCE(year_total_weight, 0) DESC')
            ->get();

        $this->chartData = $drivers->map(function ($d) {
            $name = trim(($d->employee->name ?? '') . ' ' . ($d->employee->surname ?? ''));
            $name = $name !== '' ? $name : ($d->name ?? ('Driver #' . $d->id));

            return [$name, (float) ($d->year_total_weight ?? 0)];
        })->values()->all();
    }


    public function exportDriversPerformanceCSV(Excel $excel){
        return $excel->download(new DriversPerformanceExport($this->from, $this->to, $this->filter), 'drivers.csv', Excel::CSV);
    }
    public function exportDriversPerformancePDF(Excel $excel){
        return $excel->download(new DriversPerformanceExport($this->from, $this->to, $this->filter), 'drivers.pdf', Excel::DOMPDF);
    }
    public function exportDriversPerformanceExcel(Excel $excel){
        return $excel->download(new DriversPerformanceExport($this->from, $this->to, $this->filter), 'drivers.xlsx');
    }

    public function calculateVolumeLosses($selected_driver){

         $vol_loss_percentage = Null; 

        if (($selected_driver->total_volume_loss && is_numeric($selected_driver->total_volume_loss) && $selected_driver->total_volume_loss > 0) && ($selected_driver->total_volume && is_numeric($selected_driver->total_volume) && $selected_driver->total_volume > 0)) {
            $vol_loss_percentage = ($selected_driver->total_volume_loss / $selected_driver->total_volume ) * 100;
        }

         return $vol_loss_percentage ? $vol_loss_percentage."%" : "";
      
    }
    public function calculateTonnageLosses($selected_driver){
       
        $tonnage_loss_percentage = Null;

        if (($selected_driver->total_tonnage_loss && is_numeric($selected_driver->total_tonnage_loss)  && $selected_driver->total_tonnage_loss > 0) && ($selected_driver->total_tonnage && is_numeric($selected_driver->total_tonnage) && $selected_driver->total_tonnage > 0)) {
            $tonnage_loss_percentage = ($selected_driver->total_tonnage_loss / $selected_driver->total_tonnage ) * 100;
        }

        return $tonnage_loss_percentage ? $tonnage_loss_percentage."%" : "";
    }

    public function calculateTotalRevenue($id)
    {
        if (is_null($id)) return;

        if (!$this->currency) {
            return "Currency not set.";
        }

        $total_freight = Null;

        if ($this->from && $this->to) {
            $default_currency_trips_freight = Trip::where('driver_id', $id)
                ->whereBetween($this->filter, [$this->from, $this->to])
                ->where('currency_id', $this->currency->id)
                ->whereNotNull('freight')
                ->where('freight', '!=', 0)
                ->sum('freight');

            $other_currency_trips_freight = Trip::where('driver_id', $id)
                ->whereBetween($this->filter, [$this->from, $this->to])
                ->where('currency_id', '!=', $this->currency->id)
                ->whereNotNull('exchange_customer_freight')
                ->where('exchange_customer_freight', '!=', 0)
                ->sum('exchange_customer_freight');
        } else {
            $default_currency_trips_freight = Trip::where('driver_id', $id)
                ->whereMonth($this->filter, Carbon::now()->month)
                ->whereYear($this->filter, date('Y'))
                ->where('currency_id', $this->currency->id)
                ->whereNotNull('freight')
                ->where('freight', '!=', 0)
                ->sum('freight');

            $other_currency_trips_freight = Trip::where('driver_id', $id)
                ->whereMonth($this->filter, Carbon::now()->month)
                ->whereYear($this->filter, date('Y'))
                ->where('currency_id', '!=', $this->currency->id)
                ->whereNotNull('exchange_customer_freight')
                ->where('exchange_customer_freight', '!=', 0)
                ->sum('exchange_customer_freight');
        }

        $total_freight = $default_currency_trips_freight + $other_currency_trips_freight;

        return $this->currency->symbol . number_format($total_freight, 2);
    }

            // RAW total fuel
    private function getTotalFuel($id)
    {
        if (!isset($this->from, $this->to)) return 0;

        $dateColumn = $this->filter === "start_date" ? "date" : $this->filter;

        return Shift::whereBetween($dateColumn, [$this->from, $this->to])
            ->where('driver_id', $id)
            ->sum('total_fuel');
    }

    // RAW total distance
    private function getTotalDistance($id)
    {
        if (!isset($this->from, $this->to)) return 0;

        $dateColumn = $this->filter === "start_date" ? "date" : $this->filter;

        return Shift::whereBetween($dateColumn, [$this->from, $this->to])
            ->where('driver_id', $id)
            ->sum('actual_mileage');
    }
    // RAW total hours
    private function getTotalHours($id)
    {
        if (!isset($this->from, $this->to)) return 0;

        $dateColumn = $this->filter === "start_date" ? "date" : $this->filter;

        return Shift::whereBetween($dateColumn, [$this->from, $this->to])
            ->where('driver_id', $id)
            ->sum('actual_hours');
    }

    // Existing public display functions
    public function calculateShiftsFuel($id)
    {
        return number_format($this->getTotalFuel($id));
    }

    public function calculateShiftsDistance($id)
    {
        return number_format($this->getTotalDistance($id));
    }
    public function calculateShiftsHours($id)
    {
        return number_format($this->getTotalHours($id));
    }

    // NEW: Fuel consumption function (Km per L)
    public function calculateFuelConsumptionMileage($id)
    {
        $fuel = $this->getTotalFuel($id);
        $distance = $this->getTotalDistance($id);

        if ($fuel <= 0 || $distance <= 0) {
            return ;
        }

        $KPerL = $distance / $fuel; // or $fuel / $distance * 100 for L/100km
        return number_format($KPerL, 2);
    }
    public function calculateFuelConsumptionHours($id)
    {
        $fuel = $this->getTotalFuel($id);
        $hours = $this->getTotalHours($id);

        if ($fuel <= 0 || $hours <= 0) {
            return ;
        }

        $HPerL =  $hours / $fuel; // or $fuel / $hours * 100 for L/100H
        return number_format($HPerL, 2);
    }

    public function render()
    {
        if (isset($this->from) && isset($this->to) ) {

            return view('livewire.drivers.performance', [
                'drivers' => DB::table('trips')
                    ->select(
                        'trips.driver_id',
                        DB::raw('count(*) as total_trips'),
                        DB::raw("
                            SUM(
                                CASE 
                                    WHEN trips.starting_mileage IS NOT NULL AND trips.ending_mileage IS NOT NULL 
                                    THEN trips.ending_mileage - trips.starting_mileage 
                                    ELSE trips.distance 
                                END
                            ) as total_kilometers
                        "),
                        DB::raw("
                            SUM(
                                CASE 
                                    WHEN trips.starting_hours IS NOT NULL AND trips.ending_hours IS NOT NULL 
                                    THEN trips.ending_hours - trips.starting_hours
                                    ELSE trips.hours 
                                END
                            ) as total_hours
                        "),
                        DB::raw('sum(litreage_at_20) as total_volume'),
                        DB::raw('sum(weight) as total_tonnage'),
                        DB::raw('sum(delivery_notes.loaded_litreage_at_20 - delivery_notes.offloaded_litreage_at_20) as total_volume_loss'),
                        DB::raw('sum(delivery_notes.loaded_weight - delivery_notes.offloaded_weight) as total_tonnage_loss'),
                        DB::raw("
                            SUM(
                                CASE 
                                    WHEN fuels.quantity IS NOT NULL THEN fuels.quantity
                                    ELSE COALESCE(trips.trip_fuel, 0)
                                END
                            ) as total_fuel_quantity
                        "),
                        DB::raw('avg(trips.fuel_consumption_mileage) as avg_fuel_consumption_mileage'),
                        DB::raw('avg(trips.fuel_consumption_hours) as avg_fuel_consumption_hours')
                    )
                    ->join('delivery_notes', 'trips.id', '=', 'delivery_notes.trip_id')
                    ->join('drivers', 'trips.driver_id', '=', 'drivers.id')
                    ->leftJoin('fuels', 'trips.id', '=', 'fuels.trip_id')
                    ->where('drivers.archive', '=', false)
                    ->where('trips.trip_status', '=', 'Offloaded')
                    ->whereBetween('trips.'.$this->filter,[$this->from, $this->to] )
                    ->whereNull('trips.deleted_at')
                    ->where('trips.authorization', 'approved')
                    ->groupBy('trips.driver_id')
                    ->orderByDesc('total_trips')
                    ->paginate(10)
            ]);

        }else{
            return view('livewire.drivers.performance', [
                'drivers' => DB::table('trips')
                    ->select(
                        'trips.driver_id',
                        DB::raw('count(*) as total_trips'),
                        DB::raw("
                            SUM(
                                CASE 
                                    WHEN trips.starting_mileage IS NOT NULL AND trips.ending_mileage IS NOT NULL 
                                    THEN trips.ending_mileage - trips.starting_mileage 
                                    ELSE trips.distance 
                                END
                            ) as total_kilometers
                        "),
                        DB::raw("
                            SUM(
                                CASE 
                                    WHEN trips.starting_hours IS NOT NULL AND trips.ending_hours IS NOT NULL 
                                    THEN trips.ending_hours - trips.starting_hours 
                                    ELSE trips.hours 
                                END
                            ) as total_hours
                        "),
                        DB::raw('sum(litreage_at_20) as total_volume'),
                        DB::raw('sum(weight) as total_tonnage'),
                        DB::raw('sum(delivery_notes.loaded_litreage_at_20 - delivery_notes.offloaded_litreage_at_20) as total_volume_loss'),
                        DB::raw('sum(delivery_notes.loaded_weight - delivery_notes.offloaded_weight) as total_tonnage_loss'),
                        DB::raw("
                            SUM(
                                CASE 
                                    WHEN fuels.quantity IS NOT NULL THEN fuels.quantity
                                    ELSE COALESCE(trips.trip_fuel, 0)
                                END
                            ) as total_fuel_quantity
                        "),
                        DB::raw('avg(trips.fuel_consumption_mileage) as avg_fuel_consumption_mileage'),
                        DB::raw('avg(trips.fuel_consumption_hours) as avg_fuel_consumption_hours')
                    )
                    ->join('delivery_notes', 'trips.id', '=', 'delivery_notes.trip_id')
                    ->join('drivers', 'trips.driver_id', '=', 'drivers.id')
                    ->leftJoin('fuels', 'trips.id', '=', 'fuels.trip_id')
                    ->where('drivers.archive', '=', false)
                    ->where('trips.trip_status', '=', 'Offloaded')
                    ->whereYear('trips.'.$this->filter, date('Y'))
                    ->whereMonth('trips.'.$this->filter, now()->month)
                    ->whereNull('trips.deleted_at')
                    ->where('trips.authorization', 'approved')
                    ->groupBy('trips.driver_id')
                    ->orderByDesc('total_trips')
                    ->paginate(10)
            ]);
        }
    }
}
