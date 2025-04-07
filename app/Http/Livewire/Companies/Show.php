<?php

namespace App\Http\Livewire\Companies;

use App\Models\Company;
use Livewire\Component;

class Show extends Component
{
    public $company;

    public function mount($id){
        $this->company = Company::find($id);
    }

    public function render()
    {
        return view('livewire.companies.show');
    }
}
