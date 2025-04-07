<?php

namespace App\Http\Livewire\Logs;

use App\Models\Log;
use Livewire\Component;

class Show extends Component
{

    public $logs;
    public $log_id;

    public function mount($id){
        $this->log = Log::find($id);
    }

    public function render()
    {
        return view('livewire.logs.show');
    }
}
