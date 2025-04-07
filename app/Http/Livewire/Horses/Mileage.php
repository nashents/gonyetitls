<?php

namespace App\Http\Livewire\Horses;

use App\Models\Horse;
use Livewire\Component;
use Maatwebsite\Excel\Excel;
use App\Exports\HorsesMileageExport;

class Mileage extends Component
{

    public $horses;
    public $horse_id;
    public $mileage;
    public $next_service;
    public $prev_service;
    public $prev_service_date;

    

    public function mount(){
        $this->horses = Horse::orderBy('registration_number','asc')->get();
    }

 


    public function exportHorsesMileageCSV(Excel $excel){

        return $excel->download(new HorsesMileageExport, 'horses.csv', Excel::CSV);
    }
    public function exportHorsesMileagePDF(Excel $excel){

        return $excel->download(new HorsesMileageExport, 'horses.pdf', Excel::DOMPDF);
    }
    public function exportHorsesMileageExcel(Excel $excel){
        return $excel->download(new HorsesMileageExport, 'horses.xlsx');
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
    ];
    


    public function edit($id){
        $horse = Horse::find($id);
        $this->mileage = $horse->mileage;
        $this->next_service = $horse->next_service;
        $this->prev_service = $horse->prev_service;
        $this->prev_service_date = $horse->prev_service_date;
        $this->horse_id = $id;
        $this->dispatchBrowserEvent('show-mileageModal');
       
    }

    public function update(){
        $horse = Horse::find($this->horse_id);
        $horse->mileage = $this->mileage;
        $horse->next_service = $this->next_service;
        $horse->prev_service = $this->prev_service;
        $horse->prev_service_date = $this->prev_service_date;
        $horse->update();

        $this->dispatchBrowserEvent('hide-mileageModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Horse Service Details Updated Successfully!!"
        ]);
    }

    public function render()
    {
        $this->horses = Horse::orderBy('registration_number','asc')->get();
        return view('livewire.horses.mileage',[
            'horses' => $this->horses
        ]);
    }
}
