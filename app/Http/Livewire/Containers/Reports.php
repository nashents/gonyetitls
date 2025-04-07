<?php

namespace App\Http\Livewire\Containers;

use Livewire\Component;
use App\Models\Container;

class Reports extends Component
{
    public $search = NULL;
    public $to;
    public $from;
    public $containers;

    public function search(){

        $this->search = TRUE;
        $this->containers = Container::whereBetween('created_at',[$this->from, $this->to] )
        ->latest()->get();


    }
    public function render()
    {
        return view('livewire.containers.reports',[
            'containers' => $this->containers
        ]);
    }
}
