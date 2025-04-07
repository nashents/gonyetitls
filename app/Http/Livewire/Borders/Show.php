<?php

namespace App\Http\Livewire\Borders;

use App\Models\Border;
use Livewire\Component;

class Show extends Component
{
    public $border;
     
    public function mount($id){
        $this->border = Border::find($id);
    }
    public function render()
    {
        return view('livewire.borders.show');
    }
}
