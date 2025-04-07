<?php

namespace App\Http\Livewire\Routes;

use Livewire\Component;
use App\Models\Route;
use App\Exports\TripsExport;
use Maatwebsite\Excel\Excel;

class Trips extends Component
{
    public $routes; 
    public $route_id; 
    public $trips; 
    public $trip_id;
    public $search_id;
    public $from;
    public $to;

    public function exportTripsCSV(Excel $excel){

        return $excel->download(new TripsExport($this->search_id,$this->category,$this->from,$this->to), 'trips.csv', Excel::CSV);
    }
    public function exportTripsPDF(Excel $excel){

        return $excel->download(new TripsExport($this->search_id,$this->category,$this->from,$this->to), 'trips.pdf', Excel::DOMPDF);
    }
    public function exportTripsExcel(Excel $excel){
        return $excel->download(new TripsExport($this->search_id,$this->category,$this->from,$this->to), 'trips.xlsx');
    }
    
       public function mount($id){
        $this->route = Route::find($id);
        $this->search_id = $id;
        $this->category = 'Route';
        $this->trips = $this->route->trips;
    }
    
    public function render()
    {
        return view('livewire.routes.trips',[
            
        ]);
    }
}
