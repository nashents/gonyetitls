<?php

namespace App\Http\Livewire\Recoveries;

use Livewire\Component;
use App\Models\Recovery;

class Show extends Component
{
    public $recovery;
    public $recovery_id;


    public function mount($id){
        $this->recovery = Recovery::find($id);
        $this->recovery_id = $id;
    }
    public function render()
    {
        return view('livewire.recoveries.show');
    }
}
