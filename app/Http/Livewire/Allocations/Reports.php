<?php

namespace App\Http\Livewire\Allocations;

use Livewire\Component;
use App\Models\Employee;
use App\Models\Allocation;

class Reports extends Component
{
    public $search = NULL;
    public $to;
    public $from;
    public $allocations;
    public $type;
    public $employee_id;
    public $selectedType = NULL;
    public $selectedEmployee = NULL;
    public function mount(){
        $this->employees = collect();
    }

    public function updatedSelectedType($type)
    {
        if (!is_null($type) ) {
            if ($type == "individual") {
                $this->selectedEmployee = TRUE;
                $this->employees = Employee::all();
            }elseif ($type == "all") {
                $this->selectedEmployee = NULL;

            }

        }

    }
    public function search(){

        $this->search = TRUE;
        if (!is_null($this->selectedEmployee)) {
          $this->allocations = Allocation::where('employee_id', $this->employee_id)
            ->whereBetween('created_at',[$this->from, $this->to] )
            ->latest()->get();
        }else{
            $this->allocations = Allocation::whereBetween('created_at',[$this->from, $this->to] )
            ->latest()->get();
        }
    }
    public function render()
    {
        return view('livewire.allocations.reports');
    }
}
