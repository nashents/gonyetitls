<?php

namespace App\Http\Livewire\Dispatches;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Preview extends Component
{
    public $company;
    public $dispatch;

    public function mount($dispatch){
        $this->company = Auth::user()->employee->company;
        $this->dispatch = $dispatch;
    }

    public function render()
    {
        return view('livewire.dispatches.preview');
    }
}
