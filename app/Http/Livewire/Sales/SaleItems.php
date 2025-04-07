<?php

namespace App\Http\Livewire\Sales;

use App\Models\Sale;
use Livewire\Component;
use App\Models\SaleItem;

class SaleItems extends Component
{
    public $income_streams;
    public $income_stream;
    public $products;
    public $product;
    public $selectedproduct;
    public $income_stream_id;
    public $reason;
    public $qty;
    public $amount;
    public $trips;
    public $subtotal;
    public $subtotal_incl;
    public $item_subtotal;
    public $added_item_subtotal;
    public $deleted_item_subtotal;
    public $edited_item_subtotal;
    public $x;
    public $tax_rate;
    public $tax_accounts;
    public $selectedTax = [];
    public $tax_amount;
    public $total_tax_amount;
    public $sale_total;
    public $sale_subtotal;
    public $total;
    public $selectedTrip;
    public $description;
    public $sale;
    public $sale_id;
    public $sale_items;
    public $sale_item_id;

    public $inputs = [];
    public $i = 1;
    public $n = 1;

    public function add($i)
    {
        $i = $i + 1;
        $this->i = $i;
        array_push($this->inputs ,$i);
    }
    private function resetInputFields(){
        $this->selectedTrip = "" ;
        $this->description = "" ;
    }

    public function remove($i)
    {
        unset($this->inputs[$i]);
    }

    public function mount($id){

        $this->sale_id = $id;
        $this->sale = Sale::find($id);
        $this->sale_items = SaleItem::withTrashed()->where('sale_id',$this->sale_id)->get();

    }


   
   
    public function render()
    {
        $this->sale_items = SaleItem::where('sale_id',$this->sale_id)->get();
        return view('livewire.sales.sale-items',[
            'sale_items' => $this->sale_items
        ]);
    }
}
