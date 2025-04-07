<?php

namespace App\Http\Livewire\Horses;

use App\Models\Horse;
use Livewire\Component;
use App\Models\Currency;
use Maatwebsite\Excel\Excel;
use App\Exports\HorsesReportExport;

class Reports extends Component
{

    public $selectedFilter;
    public $to;
    public $from;
    public $horses;
    public $currencies;
    public $currency_id;

   


    public function exportHorsesCSV(Excel $excel){

        return $excel->download(new HorsesReportExport($this->selectedFilter,$this->from,$this->to), 'horses.csv', Excel::CSV);
    }
    public function exportHorsesPDF(Excel $excel){

        return $excel->download(new HorsesReportExport($this->selectedFilter,$this->from,$this->to), 'horses.pdf', Excel::DOMPDF);
    }
    public function exportHorsesExcel(Excel $excel){
        return $excel->download(new HorsesReportExport($this->selectedFilter,$this->from,$this->to), 'horses.xlsx');
    }

    public function mount(){
        $this->currencies = Currency::all();
        // $this->selectedFilter = "revenue";
        if (isset($this->from) && isset($this->to)) {
            $this->horses = Horse::query()->whereBetween('created_at',[$this->from, $this->to] )->latest()->get();
        }else{
            $this->horses = Horse::query()->get();
        }
    }

    public function updatedSelectedFilter($filter){
        if (!is_null($filter)) {
          
        }
    }

    public function render()
    {
        if (isset($this->from) && isset($this->to)) {
            $this->horses = Horse::query()->whereBetween('created_at',[$this->from, $this->to] )->get();
        }else{
            $this->horses = Horse::query()->orderBy('registration_number','asc')->get();
        }
        return view('livewire.horses.reports',[
            'horses' => $this->horses
        ]);
    }
}
