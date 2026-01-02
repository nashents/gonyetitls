<?php

namespace App\Http\Livewire\Bills;

use App\Models\Bill;
use Livewire\Component;
use Livewire\WithPagination;

class Show extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';
    public $bills;
    public $bill_id;
    public $bill;
    protected $payments;

    public function mount($id){
        $this->bill_id = $id;
        $this->bill = Bill::find($id);
    }

    public function render()
    {
      
        return view('livewire.bills.show',[
            'payments' => $this->bill->payments()->paginate(10),
        ]);
    }
}
