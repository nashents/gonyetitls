<?php

namespace App\Http\Livewire\Drivers;

use Livewire\Component;
use App\Models\Breakdown;
use Livewire\WithPagination;

class Breakdowns extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';
    public $search;
    protected $queryString = ['search'];
    private $breakdowns;
    public $driver;

    public function mount($driver){
        $this->driver = $driver;
      
    }

    public function render()
    {
        return view('livewire.drivers.breakdowns',[
            'breakdowns' => Breakdown::whereYear('date',date('Y'))
            ->where('driver_id',$this->driver->id)->paginate(10)
        ]);
    }
}
