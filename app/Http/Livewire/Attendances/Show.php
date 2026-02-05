<?php

namespace App\Http\Livewire\Attendances;

use Livewire\Component;
use App\Models\Attendance;

class Show extends Component
{
    public $attendance;
    public $attendance_registers;

    public function mount($id){
        $this->attendance = Attendance::find($id);
        $this->attendance_registers = $this->attendance->attendance_registers;
    }
    public function render()
    {
        return view('livewire.attendances.show');
    }
}
