<?php

namespace App\Http\Livewire\Vehicles;

use App\Models\Vehicle;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Session;

class Archived extends Component
{

    use WithPagination;

    protected $paginationTheme = 'bootstrap';
    public $search;
    protected $queryString = ['search'];

    private $vehicles;
    public $vehicle_id;
  
    public function mount(){
        
    }

    public function restore($id){
        $this->vehicle_id = $id;
        $this->dispatchBrowserEvent('show-restoreModal');
    }
    public function update(){
        $vehicle =  Vehicle::withTrashed()->find($this->vehicle_id);
        $vehicle->archive = 0;
        $vehicle->status = 1 ;
        $vehicle->service = 0;
        $vehicle->update();
        Session::flash('success','Vehicle Restored Successfully!!');
        $this->dispatchBrowserEvent('hide-restoreModal');
        return redirect()->route('vehicles.index');
    }


    public function render()
    {
        return view('livewire.vehicles.archived',[
            'vehicles' => Vehicle::where('archive','1')->orderBy('registration_number', 'desc')->paginate(10)
        ]);
    }
}
