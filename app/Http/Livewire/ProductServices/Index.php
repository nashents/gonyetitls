<?php

namespace App\Http\Livewire\ProductServices;

use App\Models\Tax;
use App\Models\Account;
use App\Models\Product;
use Livewire\Component;
use Livewire\WithPagination;
use App\Models\ProductService;
use Illuminate\Support\Facades\Auth;

class Index extends Component
{

    use WithPagination;
    protected $paginationTheme = 'bootstrap';
    public $search;
    
    public $name;
    public $response;
    private $products;
    public $product;
    public $product_id;
    public $tax_accounts;
    public $tax_account_id;
    public $income_accounts;
    public $income_account_id;
    public $expense_accounts;
    public $expense_account_id;
    public $category;
    public $buy;
    public $sell;
    public $price;
    public $sell_price;
    public $description;

    public function mount($category){
        $this->resetPage();
        $this->category = $category;
        $this->income_accounts = Account::whereHas('account_type', function($q){
            $q->where('name', 'Income');
         })->orderBy('name','asc')->get();
         
        $this->expense_accounts = Account::whereHas('account_type.account_type_group', function($q){
            $q->where('name', 'Expenses');
         })->orderBy('name','asc')->get();

        $this->tax_accounts = Tax::whereHas('account', function ($query) {
            return $query->where('name','Value Added Tax');
        })->orderBy('name','asc')->get();
    }
    private function resetInputFields(){
        $this->name = '';
    }

    public function updated($value){
        $this->validateOnly($value);
    }

    protected $rules = [
        'name' => 'required',
    ];

    public function store(){
        // try{
            if ($this->buy == False && $this->sell == False ) {
                $this->response = "Please indicate whether you will be buying or selling this product or both.";
            }else{
                $product = new Product;
                $product->user_id = Auth::user()->id;
                $product->name = $this->name;
                $product->description = $this->description;
                $product->price = $this->price;
                $product->sell_price = $this->sell_price;
                $product->sell = $this->sell;
                $product->buy = $this->buy;
                $product->tax_id = $this->tax_account_id;
                $product->account_id = $this->income_account_id;
                $product->expense_account_id = $this->expense_account_id;
                $product->save(); 
        
                $this->dispatchBrowserEvent('hide-product_serviceModal');
                $this->resetInputFields();
                $this->dispatchBrowserEvent('alert',[
                    'type'=>'success',
                    'message'=>"Item Created Successfully!!"
                ]);
        
            }
           
            // }
            //     catch(\Exception $e){
            //     // Set Flash Message
            //     $this->dispatchBrowserEvent('alert',[
            //         'type'=>'error',
            //         'message'=>"Something went wrong while creating Item!!"
            //     ]);
            //  }
        }

        public function edit($id){
            $this->product_id = $id;
            $this->product = Product::find($id);
            $this->name = $this->product->name;
            $this->description = $this->product->description;
            $this->sell = $this->product->sell;
            $this->buy = $this->product->buy;
            $this->tax_account_id = $this->product->tax_id;
            $this->expense_account_id = $this->product->expense_account_id;
            $this->income_account_id = $this->product->account_id;
            $this->price = $this->product->price;
            $this->dispatchBrowserEvent('show-product_serviceEditModal');

        }

        public function update(){
            try{
        
                $product = Product::find($this->product_id);
                $product->name = $this->name;
                $product->description = $this->description;
                $product->price = $this->price;
                $product->sell = $this->sell;
                $product->buy = $this->buy;
                $product->tax_id = $this->tax_account_id;
                $product->account_id = $this->income_account_id;
                $product->expense_account_id = $this->expense_account_id;
                $product->update(); 
        
                $this->dispatchBrowserEvent('hide-product_serviceEditModal');
                $this->resetInputFields();
                $this->dispatchBrowserEvent('alert',[
                    'type'=>'success',
                    'message'=>"Item Updated Successfully!!"
                ]);
        
                }
                    catch(\Exception $e){
                    // Set Flash Message
                    $this->dispatchBrowserEvent('alert',[
                        'type'=>'error',
                        'message'=>"Something went wrong while updating Item!!"
                    ]);
                 }
            }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {

        if ($this->category == "invoices") {
             $this->sell = True;
             $this->buy = False;
             if (filled($this->search)) {
                 return view('livewire.product-services.index',[
                'products' =>   Product::where('sell',True)
                ->where('name','like', '%'.$this->search.'%')
                ->orWhere('description','like', '%'.$this->search.'%')
                ->orWhere('sell_price','like', '%'.$this->search.'%')
                ->orderBy('name','asc')->paginate(10)
                ]);
             }else{
                 return view('livewire.product-services.index',[
                'products' =>   Product::where('sell',True)->orderBy('name','asc')->paginate(10)
                ]);
             }
            
          
        }elseif ($this->category == "bills") {
             $this->buy = True;
             $this->sell = False;
             if (filled($this->search)) {
                return view('livewire.product-services.index',[
                'products' =>  Product::where('buy',True)
                 ->where('name','like', '%'.$this->search.'%')
                ->orWhere('description','like', '%'.$this->search.'%')
                ->orWhere('price','like', '%'.$this->search.'%')
                ->orderBy('name','asc')->paginate(10)
                ]);
             }else{
                return view('livewire.product-services.index',[
                'products' =>  Product::where('buy',True)->orderBy('name','asc')->paginate(10)
                ]);
             }
              
           
        }
        
    }
}
