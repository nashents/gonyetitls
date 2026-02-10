<?php

namespace App\Http\Livewire\Audits;

use App\Models\Trip;
use Livewire\Component;

class All extends Component
{

    public $audits;
    public $modified_audits;
    public $latest_audit;
    public $trip;

    public function mount(){
          
            $this->audits = $this->trip->audits;
            $this->latest_audit =  $this->trip->audits()->latest()->first();
            $this->modified_audits = $this->latest_audit->getModified();
       
    }

    public function render()
    {
        return view('livewire.audits.index');
    }
}
