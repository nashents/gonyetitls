<?php

namespace App\Http\Livewire\Assets;

use Livewire\Component;

class Show extends Component
{
    public $asset;

    public function mount($asset){
        $this->asset = $asset;
    }
    public function render()
    {
        return view('livewire.assets.show');
    }
}
