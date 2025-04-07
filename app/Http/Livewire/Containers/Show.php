<?php

namespace App\Http\Livewire\Containers;

use Livewire\Component;
use App\Models\Container;

class Show extends Component
{
    public $containers;
    public $container;

    public function mount($id){
        $this->container = Container::find($id);
    }
    public function render()
    {
        return view('livewire.containers.show');
    }
}
