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
    public $user;
    public $employee;
    public $role_names = [];
    public $department_names = [];
    public $rank_names = [];

    public function mount($id){
        
        $this->shift_id = $id;
        $this->shift = Shift::find($id);
        $this->user = Auth::user();
        $this->employee = $this->user->employee;
        $this->company = $this->employee->company;
         foreach($this->employee->departments as $department) {
            $this->department_names[] = $department->name;
        }
    
        foreach($this->user->roles as $role) {
            $this->role_names[] = $role->name;
        }
    
        foreach($this->employee->ranks as $rank) {
            $this->rank_names[] = $rank->name;
        }
       
    }
    public function render()
    {
        return view('livewire.shifts.trips',[
            'trips' => Trip::where('shift_id',$this->shift_id)->paginate(10)
        ]);
    }
}
