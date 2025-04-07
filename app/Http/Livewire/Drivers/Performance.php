<?php

namespace App\Http\Livewire\Drivers;

use App\Models\Driver;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Excel;
use Illuminate\Support\Facades\DB;
use App\Exports\DriversPerformanceExport;

class Performance extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    private $drivers;
    public $filter;
    public $from;
    public $to;

    public function mount(){
        $this->filter = "created_at";
        // $this->drivers = Driver::orderBy('registration_number','asc')->get();
       
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

    public function render()
    {
        if (isset($this->from) && isset($this->to) ) {
            return view('livewire.drivers.performance',[
                'drivers' => DB::table('trips')
                ->select('trips.driver_id', DB::raw('count(*) as total_trips'), DB::raw('sum(trips.ending_mileage - trips.starting_mileage) as total_kilometers') , DB::raw('sum(litreage_at_20) as total_volume') , DB::raw('sum(weight) as total_tonnage'), DB::raw('sum(delivery_notes.loaded_litreage_at_20 - delivery_notes.offloaded_litreage_at_20) as total_volume_loss') , DB::raw('sum(delivery_notes.loaded_weight - delivery_notes.offloaded_weight) as total_tonnage_loss'))
                ->join('delivery_notes', 'trips.id', '=', 'delivery_notes.trip_id')
                ->join('drivers', 'trips.driver_id', '=', 'drivers.id')
                ->where('drivers.archive', '=', false)
                ->where('trips.trip_status', '=', 'Offloaded')
                ->whereBetween('trips.'.$this->filter,[$this->from, $this->to] )
                ->where('trips.deleted_at', Null)
                ->where('trips.authorization','approved')
                ->groupBy('trips.driver_id')
                ->orderByDesc('total_trips')
                ->paginate(10)
            ]);
           
        }else{
            return view('livewire.drivers.performance',[
                'drivers' => DB::table('trips')
                ->select('trips.driver_id', DB::raw('count(*) as total_trips'), DB::raw('sum(trips.ending_mileage - trips.starting_mileage) as total_kilometers') , DB::raw('sum(litreage_at_20) as total_volume') , DB::raw('sum(weight) as total_tonnage'), DB::raw('sum(delivery_notes.loaded_litreage_at_20 - delivery_notes.offloaded_litreage_at_20) as total_volume_loss') , DB::raw('sum(delivery_notes.loaded_weight - delivery_notes.offloaded_weight) as total_tonnage_loss'))
                ->join('delivery_notes', 'trips.id', '=', 'delivery_notes.trip_id')
                ->join('drivers', 'trips.driver_id', '=', 'drivers.id')
                ->where('drivers.archive', '=', false)
                ->where('trips.trip_status', '=', 'Offloaded')
                ->whereYear('trips.start_date', date('Y'))
                ->whereMonth('trips.start_date', now()->month)
                ->where('trips.deleted_at', Null)
                ->where('trips.authorization','approved')
                ->groupBy('trips.driver_id')
                ->orderByDesc('total_trips')
                ->paginate(10)
            
            ]);
        }
    }
}
