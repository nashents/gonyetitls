<?php

namespace App\Http\Livewire\Assignments;

use Livewire\Component;
use App\Models\Assignment;

class Reports extends Component
{

    public $search = NULL;
    public $to;
    public $from;
    public $assignments;

    public function search(){

        $this->search = TRUE;
        $this->assignments = Assignment::whereBetween('created_at',[$this->from, $this->to] )
        ->latest()->get();

    }
    public function render()
    {
        return view('livewire.assignments.reports');
    }
}
