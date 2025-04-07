<?php

namespace App\Http\Livewire\Fuels;

use App\Models\Fuel;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class Deleted extends Component
{

    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    private $fuels;
    public $fuel_id;

    public function mount(){
       

    }

    public function showRestore($id){
        $this->fuel_id = $id;
        $this->dispatchBrowserEvent('show-fuelRestoreModal');
    }
    public function update(){
        Fuel::withTrashed()->find($this->fuel_id)->restore();
        
        $this->dispatchBrowserEvent('hide-fuelRestoreModal');
       
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Fuel Order Restored Successfully!!"
        ]);
        
      
    }
    public function render()
    {
        $this->fuels = Fuel::onlyTrashed()->latest()->paginate();
        return view('livewire.fuels.deleted',[
            'fuels' => $this->fuels
        ]);
    }
}
