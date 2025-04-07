<?php

namespace App\Http\Livewire\Horses;

use App\Models\Horse;
use Livewire\Component;
use Maatwebsite\Excel\Excel;
use App\Exports\HorsesAgeExport;

class Age extends Component
{

    public $horses;

    public function mount(){
        $this->horses = Horse::orderBy('registration_number','asc')->get();
    }

    public function exportHorsesAgeCSV(Excel $excel){

        return $excel->download(new HorsesAgeExport, 'horses.csv', Excel::CSV);
    }
    public function exportHorsesAgePDF(Excel $excel){

        return $excel->download(new HorsesAgeExport, 'horses.pdf', Excel::DOMPDF);
    }
    public function exportHorsesAgeExcel(Excel $excel){
        return $excel->download(new HorsesAgeExport, 'horses.xlsx');
    }
    public function render()
    {
        return view('livewire.horses.age');
    }
}
