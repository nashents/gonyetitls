<?php

namespace App\Http\Livewire\Trailers;

use App\Models\Trailer;
use Livewire\Component;
use Maatwebsite\Excel\Excel;
use App\Exports\TrailersAgeExport;

class Age extends Component
{
    public $trailers;

    public function mount(){
        $this->trailers = Trailer::orderBy('registration_number','asc')->get();
    }

    public function exportTrailersAgeCSV(Excel $excel){

        return $excel->download(new TrailersAgeExport, 'trailers.csv', Excel::CSV);
    }
    public function exportTrailersAgePDF(Excel $excel){

        return $excel->download(new TrailersAgeExport, 'trailers.pdf', Excel::DOMPDF);
    }
    public function exportTrailersAgeExcel(Excel $excel){
        return $excel->download(new TrailersAgeExport, 'trailers.xlsx');
    }
    public function render()
    {
        return view('livewire.trailers.age');
    }
}
