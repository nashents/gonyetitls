<?php

namespace App\Http\Livewire\Quotations;

use App\Models\Cargo;
use Livewire\Component;
use App\Models\Quotation;
use App\Models\Destination;
use App\Models\LoadingPoint;
use App\Models\OffloadingPoint;
use App\Models\QuotationProduct;

class Products extends Component
{
    public $quotation;
    public $quotation_id;
    public $quotation_items;
    public $quotation_item_id;
    public $loading_points;
    public $loading_point_id;
    public $offloading_points;
    public $offloading_point_id;
    public $bank_accounts;
    public $bank_account_id;
    public $cargos;
    public $cargo_id;
    public $currencies;
    public $destinations;
    public $currency_id;
    public $vat;
    public $x;
    public $date;
    public $expiry;
    public $subheading;
    public $memo;
    public $footer;
    public $subtotal;
    public $add_products_subtotal;
    public $deleted_product_subtotal;
    public $edited_product_subtotal;
    public $total;
    public $from;
    public $description;
    public $to;
    public $weight;
    public $rate;
    public $freight;
    public $quotation_item;


    public $user_id;

public $inputs = [];
public $i = 1;
public $n = 1;

public function add($i)
{
    $i = $i + 1;
    $this->i = $i;
    array_push($this->inputs ,$i);
}
public function remove($i)
{
    unset($this->inputs[$i]);
}
private function resetInputFields(){
    $this->from = "" ;
    $this->to = "" ;
    $this->loading_point_id = "" ;
    $this->offloading_point_id = "" ;
    $this->cargo_id = "" ;
    $this->weight = "" ;
    $this->rate = "" ;
    $this->freight = "" ;
}

    public function mount($id){
        $this->quotation_id = $id;
        $this->quotation = Quotation::find($id);
        $this->vat = $this->quotation->vat;
        $this->total = $this->quotation->total;
        $this->subtotal = $this->quotation->subtotal;
        $this->quotation_items = $this->quotation->quotation_items;
        $this->cargos = Cargo::orderBy('name','asc')->get();
        $this->loading_points = LoadingPoint::orderBy('name','asc')->get();
        $this->offloading_points = OffloadingPoint::orderBy('name','asc')->get();
        $this->destinations = Destination::with('country')->get()->sortBy('city')->sortBy('country.name');
    }

public function store(){

    if (isset($this->cargo_id )) {
    foreach ($this->cargo_id as $key => $value) {
        $quotation_item = new QuotationProduct;
        $quotation_item->quotation_id =  $this->quotation_id;
        if (isset($this->cargo_id[$key])) {
            $quotation_item->cargo_id = $this->cargo_id[$key];
        }
        if (isset($this->description[$key])) {
            $quotation_item->description = $this->description[$key];
        }
        if (isset($this->to[$key])) {
            $quotation_item->to = $this->to[$key];
        }
        if (isset($this->from[$key])) {
            $quotation_item->from = $this->from[$key];
        }
        if (isset($this->loading_point_id[$key])) {
            $quotation_item->loading_point_id = $this->loading_point_id[$key];
        }
        if (isset($this->offloading_point_id[$key])) {
            $quotation_item->offloading_point_id = $this->offloading_point_id[$key];
        }
        if (isset($this->rate[$key])) {
            $quotation_item->rate = $this->rate[$key];
        }
        if (isset($this->weight[$key])) {
            $quotation_item->weight = $this->weight[$key];
        }
        if (isset($this->freight[$key])) {
            $quotation_item->freight = $this->freight[$key];
        }
       
        $quotation_item->save();

      
        $this->add_products_subtotal = $this->add_products_subtotal + $this->freight[$key];

    }
        $this->x = $this->add_products_subtotal + $this->subtotal;
}

    if ($this->vat != "") {
        $this->total = $this->x + ($this->x * ($this->vat/100));
        $quotation =  Quotation::find($this->quotation->id);
        $quotation->total = $this->total;
        $quotation->subtotal = $this->x;
        $quotation->vat = $this->vat;
        $quotation->update();
    }else{
        $this->total = $this->x;
        $quotation =  Quotation::find($this->quotation->id);
        $quotation->total = $this->total;
        $quotation->subtotal = $this->x;
        $quotation->vat = $this->vat;
        $quotation->update();
    }

    $this->dispatchBrowserEvent('hide-addQuotationProductModal');
    $this->resetInputFields();
    $this->dispatchBrowserEvent('alert',[
        'type'=>'success',
        'message'=>"Quotation Product(s) Added Successfully!!"
    ]);
   
}



    public function removeShow($quotation_item_id){
        $this->quotation_item = QuotationProduct::find($quotation_item_id);
        $this->vat = $this->quotation->vat;
        $this->total = $this->quotation->total;
        $this->subtotal = $this->quotation->subtotal;
        $this->quotation_items = $this->quotation->quotation_items;
        $this->dispatchBrowserEvent('show-removeModal');
    }

    public function removeQuotationProduct(){ 

        $quotation = $this->quotation_item->quotation;
        $this->deleted_product_subtotal = $this->subtotal - $this->quotation_item->freight;

        if ($this->vat != "") {
            $this->total = $this->deleted_product_subtotal + ( $this->deleted_product_subtotal * ($this->vat/100));
            $quotation =  Quotation::find($this->quotation->id);
            $quotation->total = $this->total;
            $quotation->subtotal = $this->deleted_product_subtotal;
            $quotation->vat = $this->vat;
            $quotation->update();
        }else{
            $this->total = $this->deleted_product_subtotal;
            $quotation =  Quotation::find($this->quotation->id);
            $quotation->total = $this->total;
            $quotation->subtotal = $this->deleted_product_subtotal;
            $quotation->vat = $this->vat;
            $quotation->update();
        }
        $this->quotation_item->delete();
        $this->dispatchBrowserEvent('hide-removeModal');
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Quotation Product Deleted Successfully!!"
        ]);

   

    }

    public function edit($id){
     
        $this->quotation_item = QuotationProduct::find($id);
        $this->quotation_item_id = $id;
        $this->vat = $this->quotation->vat;
        $this->total = $this->quotation->total;
        $this->subtotal = $this->quotation->subtotal;
        $this->quotation_items = $this->quotation->quotation_items;
        $this->description = $this->quotation_item->description;
        $this->freight = $this->quotation_item->freight;
        $this->dispatchBrowserEvent('show-editQuotationProductModal');
    }

    public function update(){
        $quotation_item = QuotationProduct::find($this->quotation_item_id);
        $quotation_item->description = $this->description;
        $quotation_item->freight = $this->freight;
        $quotation_item->update();
        $quotation = $quotation_item->quotation;
        $this->edited_product_subtotal = $quotation->quotation_items->sum('freight');

        if ($this->vat != "") {
            $this->total = $this->edited_product_subtotal + ( $this->edited_product_subtotal * ($this->vat/100));
            $quotation =  Quotation::find($this->quotation->id);
            $quotation->total = $this->total;
            $quotation->subtotal = $this->edited_product_subtotal;
            $quotation->vat = $this->vat;
            $quotation->update();
        }else{
            $this->total = $this->edited_product_subtotal;
            $quotation =  Quotation::find($this->quotation->id);
            $quotation->total = $this->total;
            $quotation->subtotal = $this->edited_product_subtotal;
            $quotation->vat = $this->vat;
            $quotation->update();
        }
        $this->dispatchBrowserEvent('hide-editQuotationProductModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Quotation Product Updated Successfully!!"
        ]);
    }

    public function render()
    {
        $this->quotation_items = QuotationProduct::where('quotation_id',$this->quotation_id)->get();
        return view('livewire.quotations.products',[
            'quotation_items' => $this->quotation_items
        ]);
    }
}
