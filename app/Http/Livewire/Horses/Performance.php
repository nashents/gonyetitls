<?php

namespace App\Http\Livewire\Horses;

use Carbon\Carbon;
use App\Models\Trip;
use App\Models\Horse;
use App\Models\Shift;
use App\Models\Driver;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Excel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Exports\HorsesPerformanceExport;

class Performance extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    private $horses;
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

 

    public function exportHorsesPerformanceCSV(Excel $excel){
        return $excel->download(new HorsesPerformanceExport($this->from, $this->to, $this->filter), 'horses.csv', Excel::CSV);
    }
    public function exportHorsesPerformancePDF(Excel $excel){
        return $excel->download(new HorsesPerformanceExport($this->from, $this->to, $this->filter), 'horses.pdf', Excel::DOMPDF);
    }
    public function exportHorsesPerformanceExcel(Excel $excel){
        return $excel->download(new HorsesPerformanceExport($this->from, $this->to, $this->filter), 'horses.xlsx');
    }

    public function calculateVolumeLosses($selected_horse){

         $vol_loss_percentage = Null; 

        if (($selected_horse->total_volume_loss && is_numeric($selected_horse->total_volume_loss) && $selected_horse->total_volume_loss > 0) && ($selected_horse->total_volume && is_numeric($selected_horse->total_volume) && $selected_horse->total_volume > 0)) {
            $vol_loss_percentage = ($selected_horse->total_volume_loss / $selected_horse->total_volume ) * 100;
        }

         return $vol_loss_percentage ? $vol_loss_percentage."%" : "";
      
    }

    public function calculateTonnageLosses($selected_horse){
       
        $tonnage_loss_percentage = Null;

        if (($selected_horse->total_tonnage_loss && is_numeric($selected_horse->total_tonnage_loss)  && $selected_horse->total_tonnage_loss > 0) && ($selected_horse->total_tonnage && is_numeric($selected_horse->total_tonnage) && $selected_horse->total_tonnage > 0)) {
            $tonnage_loss_percentage = ($selected_horse->total_tonnage_loss / $selected_horse->total_tonnage ) * 100;
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
            $default_currency_trips_freight = Trip::where('horse_id', $id)
                ->whereBetween($this->filter, [$this->from, $this->to])
                ->where('currency_id', $this->currency->id)
                ->whereNotNull('freight')
                ->where('freight', '!=', 0)
                ->sum('freight');

            $other_currency_trips_freight = Trip::where('horse_id', $id)
                ->whereBetween($this->filter, [$this->from, $this->to])
                ->where('currency_id', '!=', $this->currency->id)
                ->whereNotNull('exchange_customer_freight')
                ->where('exchange_customer_freight', '!=', 0)
                ->sum('exchange_customer_freight');
        } else {
            $default_currency_trips_freight = Trip::where('horse_id', $id)
                ->whereMonth($this->filter, Carbon::now()->month)
                ->whereYear($this->filter, date('Y'))
                ->where('currency_id', $this->currency->id)
                ->whereNotNull('freight')
                ->where('freight', '!=', 0)
                ->sum('freight');

            $other_currency_trips_freight = Trip::where('horse_id', $id)
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
            ->where('horse_id', $id)
            ->sum('total_fuel');
    }

    // RAW total distance
    private function getTotalDistance($id)
    {
        if (!isset($this->from, $this->to)) return 0;

        $dateColumn = $this->filter === "start_date" ? "date" : $this->filter;

        return Shift::whereBetween($dateColumn, [$this->from, $this->to])
            ->where('horse_id', $id)
            ->sum('actual_mileage');
    }
    // RAW total hours
    private function getTotalHours($id)
    {
        if (!isset($this->from, $this->to)) return 0;

        $dateColumn = $this->filter === "start_date" ? "date" : $this->filter;

        return Shift::whereBetween($dateColumn, [$this->from, $this->to])
            ->where('horse_id', $id)
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

        $HPerL = $hours / $fuel; // or $fuel / $hours * 100 for L/100H
        return number_format($HPerL, 2);
    }

    public function render()
    {
      
        if (isset($this->from) && isset($this->to) ) {

            return view('livewire.horses.performance', [
                'horses' => DB::table('trips')
                    ->select(
                        'trips.horse_id',
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
                    ->join('horses', 'trips.horse_id', '=', 'horses.id')
                    ->leftJoin('fuels', 'trips.id', '=', 'fuels.trip_id')
                    ->where('horses.archive', '=', false)
                    ->where('trips.trip_status', '=', 'Offloaded')
                    ->whereBetween('trips.'.$this->filter,[$this->from, $this->to] )
                    ->whereNull('trips.deleted_at')
                    ->where('trips.authorization', 'approved')
                    ->groupBy('trips.horse_id')
                    ->orderByDesc('total_trips')
                    ->paginate(10)
            ]);

        }else{
            return view('livewire.horses.performance', [
                'horses' => DB::table('trips')
                    ->select(
                        'trips.horse_id',
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
                    ->join('horses', 'trips.horse_id', '=', 'horses.id')
                    ->leftJoin('fuels', 'trips.id', '=', 'fuels.trip_id')
                    ->where('horses.archive', '=', false)
                    ->where('trips.trip_status', '=', 'Offloaded')
                    ->whereYear('trips.'.$this->filter, date('Y'))
                    ->whereMonth('trips.'.$this->filter, now()->month)
                    ->whereNull('trips.deleted_at')
                    ->where('trips.authorization', 'approved')
                    ->groupBy('trips.horse_id')
                    ->orderByDesc('total_trips')
                    ->paginate(10)
            ]);
        }
       
    }
}
