<?php

namespace App\Http\Livewire\Horses;

use App\Models\Trip;
use App\Models\Horse;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Excel;
use App\Exports\HorseTripExport;

class Trips extends Component
{

    use WithPagination;
    protected $paginationTheme = 'bootstrap';
    
    private $trips;
    public $horse;
    public $horse_id;

    public function mount($id){
        $this->resetPage();
        $this->horse_id = $id;
        $this->horse = Horse::find($id);

    }


    public function exportTripsCSV(Excel $excel){

        return $excel->download(new HorseTripExport($this->horse_id), 'horse_trips.csv', Excel::CSV);
    }
    public function exportTripsPDF(Excel $excel){

        return $excel->download(new HorseTripExport($this->horse_id), 'horse_trips.pdf', Excel::DOMPDF);
    }
    public function exportTripsExcel(Excel $excel){
        return $excel->download(new HorseTripExport($this->horse_id), 'horse_trips.xlsx');
    }

    public function render()
    {
        $this->trips = Trip::where('horse_id',$this->horse_id)->whereYear('start_date',date('Y'))->paginate(10);
        return view('livewire.horses.trips',[
            'trips' => $this->trips
        ]);
    }
}
