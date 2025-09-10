<?php

namespace App\Http\Livewire\Drivers;

use Livewire\Component;
use App\Models\Recovery;
use Livewire\WithPagination;

class Recoveries extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';
    public $search;
    protected $queryString = ['search'];

    private $recoveries;
    public $driver;

    public function mount($driver){
        $this->driver = $driver;
        
    }

    public function render()
    {
        return view('livewire.drivers.recoveries',[
            'recoveries' => Recovery::whereYear('date',date('Y'))
            ->where('driver_id',$this->driver->id)->paginate(10)
        ]);
    }
}
