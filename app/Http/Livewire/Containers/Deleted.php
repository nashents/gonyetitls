<?php

namespace App\Http\Livewire\Containers;

use Livewire\Component;
use App\Models\Container;

class Deleted extends Component
{
    public $containers;
    public $container_id;

    public function mount(){
        $this->containers = Container::onlyTrashed()->get();
    }
    public function render()
    {
        return view('livewire.containers.deleted');
    }
}
