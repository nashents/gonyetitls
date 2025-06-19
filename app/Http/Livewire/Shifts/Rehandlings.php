<?php

namespace App\Http\Livewire\Shifts;

use App\Models\Work;
use Livewire\Component;
use App\Models\Location;
use App\Models\Rehandling;

class Rehandlings extends Component
{

    private $rehandlings;
    public $rehandling_id;
    public $shift_id;
    public $shift;
    public $works;
    public $work_id;
    public $locations;
    public $location_id;
    public $start_time;
    public $open_hours;
    public $open_mileage;
    public $stop_time;
    public $close_hours;
    public $close_mileage;
    public $weight;
    public $freight;
    public $currency_id;

    public function mount($id){
        $this->shift_id = $id;
        $this->works = Work::orderBy('description','asc')->get();
        $this->locations = Location::orderBy('name','asc')->get();
    }

     private function resetInputFields(){
        $this->work_id = '';
        $this->location_id = '';
        $this->start_time = '';
        $this->open_hours = '';
        $this->open_mileage = '';
        $this->stop_time = '';
        $this->close_hours = '';
        $this->close_mileage = '';
        $this->weight = '';
        $this->freight = '';
        $this->currency_id;
    }

    public function edit($id){
        $this->rehandling_id = $id;
        $this->rehandling = Rehandling::find($id);
        $this->start_time = $this->rehandling->start_time;
        $this->location_id = $this->rehandling->location_id;
        $this->work_id = $this->rehandling->work_id;
        $this->open_mileage = $this->rehandling->open_mileage;
        $this->open_hours = $this->rehandling->open_hours;
        $this->stop_time = $this->rehandling->stop_time;
        $this->close_hours = $this->rehandling->close_hours;
        $this->close_mileage = $this->rehandling->close_mileage;
        $this->dispatchBrowserEvent('show-rehandlingEditModal');
    }

    public function update(){
        $rehandling = Rehandling::find($this->rehandling_id);
        $rehandling->location_id = $this->location_id;
        $rehandling->work_id = $this->work_id;
        $rehandling->start_time = $this->start_time;
        $rehandling->open_hours = $this->open_hours;
        $rehandling->open_mileage = $this->open_mileage;
        $rehandling->close_mileage = $this->close_mileage;
        $rehandling->close_hours = $this->close_hours;
        $rehandling->stop_time = $this->stop_time;
        $rehandling->weight = $this->weight;
        $rehandling->freight = $this->freight;
        $rehandling->update();
        $this->dispatchBrowserEvent('hide-rehandlingEditModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Rehandling Work Updated Successfully!!"
        ]);

    }

    public function render()
    {
        return view('livewire.shifts.rehandlings',[
            'rehandlings' => Rehandling::where('shift_id',$this->shift_id)->paginate(10)
        ]);
    }
}
