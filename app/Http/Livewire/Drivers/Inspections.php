<?php

namespace App\Http\Livewire\Drivers;

use Livewire\Component;
use App\Models\Checklist;
use Livewire\WithPagination;

class Inspections extends Component
{

    use WithPagination;
    protected $paginationTheme = 'bootstrap';
    public $search;
    protected $queryString = ['search'];
    
    private $checklists;
    public $driver;

    public function mount($driver){
        $this->driver = $driver;
       
    }

    public function render()
    {
        return view('livewire.drivers.inspections',[
            'checklists' => Checklist::whereYear('date',date('Y'))
            ->where('driver_id',$this->driver->id)->paginate(10)
        ]);
    }
}
