<?php

namespace App\Http\Livewire\GoodsReceiveds;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Preview extends Component
{
    public $grn ;
    public $company;

    public function mount($goods_received){
        $this->grn  = $goods_received;
        $this->company = Auth::user()->employee->company;
    }
    public function render()
    {
        return view('livewire.goods-receiveds.preview');
    }
}
