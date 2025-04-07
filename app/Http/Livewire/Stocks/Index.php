<?php

namespace App\Http\Livewire\Stocks;

use App\Models\Stock;
use Livewire\Component;
use App\Models\Inventory;
use App\Models\ClosingStock;
use App\Models\OpeningStock;
use Illuminate\Support\Facades\Auth;

class Index extends Component
{
    public $type;
    public $name;
    public $date;
    public $description;
    public $stocks;
    public $stock_id;
    public $opening_stocks;
    public $opening_stock_id;
    public $closing_stocks;
    public $closing_stock_id;


    public function mount(){
        $this->stocks = Stock::latest()->get();
        $this->opening_stocks = Stock::where('type','Opening Stock')->orderBy('created_at','desc')->get();
        $this->inventories = Inventory::where('status',1)->get();
    }

    public function updated($value){
        $this->validateOnly($value);
    }
    protected $rules = [
        'type' => 'required',
        'name' => 'required',
        'description' => 'required',
    ];

    private function resetInputFields(){
        $this->type = '';
        $this->name = '';
        $this->date = '';
        $this->description = '';
    }

    public function stockNumber(){

        if (isset(Auth::user()->company)) {
            $str = Auth::user()->company->name;
            $words = explode(' ', $str);
            if (isset($words[1][0])) {
                $initials = $words[0][0].$words[1][0];
            }else {
                $initials = $words[0][0];
            }
        }elseif (isset(Auth::user()->employee->company)) {
            $str = Auth::user()->employee->company->name;
            $words = explode(' ', $str);
            if (isset($words[1][0])) {
                $initials = $words[0][0].$words[1][0];
            }else {
                $initials = $words[0][0];
            }
        }

            $stock = Stock::orderBy('id','desc')->first();

        if (!$stock) {
            $stock_number =  $initials .'S'. str_pad(1, 5, "0", STR_PAD_LEFT);
        }else {
            $number = $stock->id + 1;
            $stock_number =  $initials .'S'. str_pad($number, 5, "0", STR_PAD_LEFT);
        }

        return  $stock_number;


    }


    public function store(){
        $stock =  new Stock;
        $stock->user_id = Auth::user()->id;
        $stock->stock_number = $this->stockNumber();
        $type = str_replace(' ', '', $this->type);
        $stock->name = $type.'/'.$this->date;
        $stock->type = $this->type;
        $stock->date = $this->date;
        $stock->description = $this->description;
        $stock->save();

        if ($this->type == "Opening Stock") {
            foreach ($this->inventories as $inventory) {
                $opening_stock = new OpeningStock;
                $opening_stock->stock_id = $stock->id;
                $opening_stock->inventory_id = $inventory->id;
                $opening_stock->qty = $inventory->qty;
                $opening_stock->currency_id = $inventory->currency_id;
                $opening_stock->rate = $inventory->rate;
                $opening_stock->value = $inventory->value;
                $opening_stock->purchase_date = $inventory->purchase_date;
                $opening_stock->save();
                }
        }elseif ($this->type == "Closing Stock") {
            foreach ($this->inventories as $inventory) {
                $closing_stock = new ClosingStock;
                $closing_stock->stock_id = $stock->id;
                $closing_stock->opening_stock_id = $this->opening_stock_id;
                $closing_stock->inventory_id = $inventory->id;
                $closing_stock->qty = $inventory->qty;
                $closing_stock->currency_id = $inventory->currency_id;
                $closing_stock->rate = $inventory->rate;
                $closing_stock->value = $inventory->value;
                $closing_stock->purchase_date = $inventory->purchase_date;
                $closing_stock->save();
                }
        }


        $this->dispatchBrowserEvent('hide-stockModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Stock Take Session Created Successfully!!"
        ]);



    }

    public function edit($id){
        $stock = Stock::find($id);
        $this->name = $stock->name;
        $this->description = $stock->description;
        $this->date = $stock->date;
        $this->type = $stock->type;
        $this->stock_id = $stock->id;
        $this->dispatchBrowserEvent('show-stockEditModal');
    }


    public function update(){
        $stock =  Stock::find($this->stock_id);
        $stock->user_id = Auth::user()->id;
        $stock->stock_number = $this->stockNumber();
        $stock->type = $this->type;
        $type = str_replace(' ', '', $this->type);
        $stock->name = $type.'/'.$this->date;
      
        $stock->date = $this->date;
        $stock->description = $this->description;
        $stock->update();

        $this->dispatchBrowserEvent('hide-stockEditModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Stock Take Session Updated Successfully!!"
        ]);
    }

    public function render()
    {
        $this->stocks = Stock::latest()->get();
        return view('livewire.stocks.index',[
            'stocks'=> $this->stocks
        ]);
    }
}
