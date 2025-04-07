<?php

namespace App\Http\Livewire\Sales;

use App\Models\Sale;
use Livewire\Component;

class Show extends Component
{
    public $sale;
    public $sales;
    public $sale_id;

    public function mount($id){
        $this->sale = Sale::withTrashed()->find($id);
    }
    public function render()
    {
        return view('livewire.sales.show');
    }
}
