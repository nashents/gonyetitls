<?php

namespace App\Http\Livewire\Drivers;

use App\Models\Trip;
use App\Models\Driver;
use Livewire\Component;
use Livewire\WithPagination;

class Trips extends Component
{

    use WithPagination;
    protected $paginationTheme = 'bootstrap';
    public $search;
    protected $queryString = ['search'];
    private $trips;
    public $driver;

    public function mount($driver){
        $this->driver = $driver;
       
    }

    public function render()
    {
         return view('livewire.drivers.trips',[
            'trips' => Trip::whereYear('start_date',date('Y'))
            ->where('driver_id',$this->driver->id)->paginate(10)
        ]);
    }
}
