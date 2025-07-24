<?php

namespace App\Http\Livewire\Shifts;

use App\Models\Trip;
use App\Models\Shift;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

class Trips extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';
    private $trips;
    public $shift;
    public $shift_id;
    public $company;

    public function mount($id){
        $this->company = Auth::user()->employee->company;
        $this->shift_id = $id;
        $this->shift = Shift::find($id);
       
    }
    public function render()
    {
        return view('livewire.shifts.trips',[
            'trips' => Trip::where('shift_id',$this->shift_id)->paginate(10)
        ]);
    }
}
