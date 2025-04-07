<?php

namespace App\Http\Livewire\Audits;

use App\Models\Trip;
use Livewire\Component;

class Index extends Component
{

    public $audits;
    public $modified_audits;
    public $latest_audit;
    public $trip;

    public function mount($id, $category){
        if ($category == 'trip') {
            $this->trip = Trip::find($id);
            $this->audits = $this->trip->audits;
            $this->latest_audit =  $this->trip->audits()->latest()->first();
            $this->modified_audits = $this->latest_audit->getModified();
        }
       
    }

    public function render()
    {
        return view('livewire.audits.index');
    }
}
