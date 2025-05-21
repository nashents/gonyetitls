<?php

namespace App\Http\Livewire\Trailers;

use App\Models\Trailer;
use Livewire\Component;
use Maatwebsite\Excel\Excel;
use App\Exports\TrailersMileageExport;

class Mileage extends Component
{

    public $trailers;
    public $trailer_id;
    public $mileage;
    public $next_service;
    public $prev_service;
    public $prev_service_date;

    public function mount(){
        $this->trailers = Trailer::orderBy('registration_number','asc')->get();
    }

    public function exportTrailersMileageCSV(Excel $excel){

        return $excel->download(new TrailersMileageExport, 'trailers_mileage_report.csv', Excel::CSV);
    }
    public function exportTrailersMileagePDF(Excel $excel){

        return $excel->download(new TrailersMileageExport, 'trailers_mileage_report.pdf', Excel::DOMPDF);
    }
    public function exportTrailersMileageExcel(Excel $excel){
        return $excel->download(new TrailersMileageExport, 'trailers_mileage_report.xlsx');
    }

    private function resetInputFields(){
        $this->mileage = '';
        $this->next_service = '';
        $this->prev_service = '';
        $this->prev_service_date = '';
     
    }
    public function updated($value){
        $this->validateOnly($value);
    }
    protected $rules = [
        'mileage' => 'required|numeric',
        'next_service' => 'required|numeric',
        'prev_service' => 'required|numeric',
        'prev_service' => 'required|numeric',
    ];
    


    public function edit($id){
        $trailer = Trailer::find($id);
        $this->mileage = $trailer->mileage;
        $this->next_service = $trailer->next_service;
        $this->prev_service = $trailer->prev_service;
        $this->prev_service_date = $trailer->prev_service_date;
        $this->trailer_id = $id;
        $this->dispatchBrowserEvent('show-mileageModal');
       
    }

    public function update(){
        $trailer = Trailer::find($this->trailer_id);
        $trailer->mileage = $this->mileage;
        $trailer->next_service = $this->next_service;
        $trailer->prev_service = $this->prev_service;
        $trailer->prev_service_date = $this->prev_service_date;
        $trailer->update();

        $this->dispatchBrowserEvent('hide-mileageModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Horse Updated Successfully!!"
        ]);
    }


    public function render()
    {
        $this->trailers = Trailer::orderBy('registration_number','asc')->get();
        return view('livewire.trailers.mileage',[
            'trailers' => $this->trailers
        ]);
    }
}
