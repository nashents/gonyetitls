<?php

namespace App\Http\Livewire\Dispatches;

use Livewire\Component;
use App\Models\Dispatch;
use Illuminate\Support\Facades\Auth;

class Approved extends Component
{

    public $dispatches;
    public $dispatch;
    public $dispatch_id;
    public $company;
    public $department;

    public function mount($department){
        $this->department = $department;
        $this->company = Auth::user()->employee->company;
        $this->dispatches  = Dispatch::where('department',$department)->where('authorization','approved')->get();
    }


    public function render()
    {
        return view('livewire.dispatches.approved');
    }
}
