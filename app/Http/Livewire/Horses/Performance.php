<?php

namespace App\Http\Livewire\Horses;

use App\Models\Horse;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Excel;
use Illuminate\Support\Facades\DB;
use App\Exports\HorsesPerformanceExport;

class Performance extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    private $horses;
    public $filter;
    public $from;
    public $to;

    public function mount(){
        $this->filter = "created_at";
        // $this->horses = Horse::orderBy('registration_number','asc')->get();
       
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
    public function render()
    {
        if (isset($this->from) && isset($this->to) ) {
            return view('livewire.horses.performance',[
                'horses' => DB::table('trips')
                ->select('trips.horse_id', DB::raw('count(*) as total_trips'), DB::raw('sum(trips.ending_mileage - trips.starting_mileage) as total_kilometers') , DB::raw('sum(litreage_at_20) as total_volume') , DB::raw('sum(weight) as total_tonnage'), DB::raw('sum(delivery_notes.loaded_litreage_at_20 - delivery_notes.offloaded_litreage_at_20) as total_volume_loss') , DB::raw('sum(delivery_notes.loaded_weight - delivery_notes.offloaded_weight) as total_tonnage_loss'))
                ->join('delivery_notes', 'trips.id', '=', 'delivery_notes.trip_id')
                ->join('horses', 'trips.horse_id', '=', 'horses.id')
                ->where('horses.archive', '=', false)
                ->where('trips.trip_status', '=', 'Offloaded')
                ->whereBetween('trips.'.$this->filter,[$this->from, $this->to] )
                ->where('trips.deleted_at', Null)
                ->where('trips.authorization','approved')
                ->groupBy('trips.horse_id')
                ->orderByDesc('total_trips')
                ->paginate(10)
            ]);
           
        }else{
            return view('livewire.horses.performance',[
                'horses' => DB::table('trips')
                ->select('trips.horse_id', DB::raw('count(*) as total_trips'), DB::raw('sum(trips.ending_mileage - trips.starting_mileage) as total_kilometers') , DB::raw('sum(litreage_at_20) as total_volume') , DB::raw('sum(weight) as total_tonnage'), DB::raw('sum(delivery_notes.loaded_litreage_at_20 - delivery_notes.offloaded_litreage_at_20) as total_volume_loss') , DB::raw('sum(delivery_notes.loaded_weight - delivery_notes.offloaded_weight) as total_tonnage_loss'))
                ->join('delivery_notes', 'trips.id', '=', 'delivery_notes.trip_id')
                ->join('horses', 'trips.horse_id', '=', 'horses.id')
                ->where('trips.trip_status', '=', 'Offloaded')
                ->where('horses.archive', '=', false)
                ->whereYear('trips.start_date', date('Y'))
                ->whereMonth('trips.start_date', now()->month)
                ->where('trips.deleted_at', Null)
                ->where('trips.authorization','approved')
                ->groupBy('trips.horse_id')
                ->orderByDesc('total_trips')
                ->paginate(10)
            
            ]);
        }
       
    }
}
