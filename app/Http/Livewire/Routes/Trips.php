<?php

namespace App\Http\Livewire\Routes;

use App\Models\Trip;
use App\Models\Route;
use Livewire\Component;
use App\Exports\TripsExport;
use Livewire\WithPagination;
use Maatwebsite\Excel\Excel;
use App\Exports\RouteTripsExport;

class Trips extends Component
{

    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $routes; 
    public $route_id; 
    private $trips; 
    public $trip_id;
    

    public function exportTripsCSV(Excel $excel){

        return $excel->download(new RouteTripsExport($this->route_id), 'route_trips.csv', Excel::CSV);
    }
    public function exportTripsPDF(Excel $excel){

        return $excel->download(new RouteTripsExport($this->route_id), 'route_trips.pdf', Excel::DOMPDF);
    }
    public function exportTripsExcel(Excel $excel){
        return $excel->download(new RouteTripsExport($this->route_id), 'route_trips.xlsx');
    }
    
       public function mount($id){
        $this->route = Route::find($id);
        $this->route_id = $id;
      
    }
    
    public function render()
    {

        
        return view('livewire.routes.trips',[
           'trips' => Trip::where('route_id',$this->route_id)->whereYear('start_date',date('Y'))->paginate(10)
        ]);
    }
}
