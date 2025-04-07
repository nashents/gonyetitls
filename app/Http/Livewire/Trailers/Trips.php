<?php

namespace App\Http\Livewire\Trailers;

use Carbon\Carbon;
use App\Models\Trailer;
use Livewire\Component;
use Maatwebsite\Excel\Excel;
use App\Exports\TrailerTripExport;

class Trips extends Component
{

    public $trips;
    public $trailer;
    public $trailer_id;

    public function mount($id){
        $this->trailer_id = $id;
        $this->trailer = Trailer::find($id);
       
    }

    public function exportTripsCSV(Excel $excel){

        return $excel->download(new TrailerTripExport($this->trailer_id), 'trailer_trips.csv', Excel::CSV);
    }
    public function exportTripsPDF(Excel $excel){

        return $excel->download(new TrailerTripExport($this->trailer_id), 'trailer_trips.pdf', Excel::DOMPDF);
    }
    public function exportTripsExcel(Excel $excel){
        return $excel->download(new TrailerTripExport($this->trailer_id), 'trailer_trips.xlsx');
    }

    public function render()
    {
         $this->trips = $this->trailer->trips()
        ->whereYear('trips.start_date', Carbon::now()->year)
        ->get();
        return view('livewire.trailers.trips');
    }
}
