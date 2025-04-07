<?php

namespace App\Http\Livewire\Purchases;

use App\Models\Product;
use Livewire\Component;
use App\Models\Category;
use App\Models\Currency;
use App\Models\Purchase;
use App\Models\PurchaseProduct;
use Illuminate\Support\Facades\Auth;

class Create extends Component
{

    public $purchase_number;
    public $purchases;
    public $purchase_id;
    public $currencies;
    public $department;
    public $currency_id;
    public $categories;
    public $selectedCategory;
    public $value;
    public $quantity;
    public $date;

    public $products;
    public $product_id;
    public $qty;
    public $rate;

    public $title;
    public $file;

    public $inputs = [];
    public $documents = [];

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

    public function purchaseNumber(){

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

        $purchase = Purchase::orderBy('id','desc')->first();

        if (!$purchase) {
            $purchase_number =  $initials .'PO'. str_pad(1, 5, "0", STR_PAD_LEFT);
        }else {
            $number = $purchase->id + 1;
            $purchase_number =  $initials .'PO'. str_pad($number, 5, "0", STR_PAD_LEFT);
        }

        return  $purchase_number;


    }
    public function mount($category){
        $this->department = $category;
        $this->products = collect();
        $this->purchases = Purchase::latest()->get();
        $this->currencies = Currency::all();
        $this->categories = Category::latest()->get();
        $this->purchase_number = $this->purchaseNumber();
    }

    public function updated($value){
        $this->validateOnly($value);
    }
    protected $rules = [
        'date' => 'required',
        'currency_id' => 'required',
        'purchase_number' => 'required',
        'quantity' => 'required',
        'selectedCategory' => 'required',
        'value' => 'required',
        'product_id.*' => 'required',
        'rate.*' => 'required',
        'qty.*' => 'required',
        'product_id.*' => 'required',
        'rate.*' => 'required',
        'qty.*' => 'required',
    ];

    public function updatedSelectedCategory($category)
    {
        if (!is_null($category) ) {
        $this->products = Product::where('category_id', $category)->orderBy('name','asc')->get();
        }
    }

    public function store(){
        $purchase = new Purchase;
        $purchase->date = $this->date;
        $purchase->department = $this->department;
        $purchase->currency_id = $this->currency_id;
        $purchase->value = $this->value;
        $purchase->quantity = $this->quantity;
        $purchase->category_id = $this->selectedCategory;
        $purchase->save();

        if (isset($this->product_id)) {
            foreach ($this->product_id as $key => $value) {
              $product = new PurchaseProduct;
              $product->purchase_id = $purchase->id;
              $product->product_id = $this->product_id[$key];
              $product->rate = $this->rate[$key];
              $product->qty = $this->qty[$key];
              $product->save();

            }
                   # code...
          }
          return redirect(route('purchases.index'));
          $this->dispatchBrowserEvent('alert',[
              'type'=>'success',
              'message'=>"Purchase Order Created Successfully!!"
          ]);
    }
    public function render()
    {
        return view('livewire.purchases.create',[
        ]);
    }
}
