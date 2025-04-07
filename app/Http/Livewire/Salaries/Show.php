<?php

namespace App\Http\Livewire\Salaries;

use App\Models\Salary;
use Livewire\Component;

class Show extends Component
{
    public $salary;

    public function mount($id){
        $this->salary = Salary::find($id);
    }
    public function render()
    {
        return view('livewire.salaries.show');
    }
}
