<?php

namespace App\Http\Livewire\Shifts;

use App\Models\Tyre;
use App\Models\Shift;
use Livewire\Component;
use App\Models\Inventory;
use App\Models\Rehandling;
use Livewire\WithPagination;

class Show extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';
    
    public $shift;
    public $shift_id;
    public $assets;
 

    public function mount($id){
        $this->shift_id = $id;
        $this->shift = Shift::find($id);
    }

    public function render()
    {
        return view('livewire.shifts.show');
    }
}
