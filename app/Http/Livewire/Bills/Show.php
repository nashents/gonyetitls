<?php

namespace App\Http\Livewire\Bills;

use App\Models\Bill;
use Livewire\Component;

class Show extends Component
{
    public $bills;
    public $bill_id;

    public function mount($id){
        $this->bill_id = $id;
        $this->bill = Bill::find($id);
    }

    public function render()
    {
        $this->bill = Bill::find($this->bill_id);
        return view('livewire.bills.show',[
            'bill' => $this->bill
        ]);
    }
}
