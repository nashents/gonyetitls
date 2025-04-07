<?php

namespace App\Http\Livewire\Requisitions;

use Livewire\Component;
use App\Models\Requisition;
use Barryvdh\DomPDF\PDF;

class Preview extends Component
{
    public $requisition;
    public $company;

    public function mount($requisition, $company){
        $this->requisition = $requisition;
        $this->company = $company;
    }


    public function render()
    {
        return view('livewire.requisitions.preview');
    }
}
