<?php

namespace App\Http\Livewire\Leaves;

use App\Models\Leave;
use Livewire\Component;

class Reports extends Component
{

    public $search = NULL;
    public $to;
    public $from;
    public $leaves;

    public function search(){

        $this->search = TRUE;
        $this->leaves = Leave::whereBetween('created_at',[$this->from, $this->to] )
        ->latest()->get();

    }
    public function render()
    {
        return view('livewire.leaves.reports');
    }
}
