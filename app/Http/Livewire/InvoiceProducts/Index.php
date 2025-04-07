<?php

namespace App\Http\Livewire\InvoiceProducts;

use Livewire\Component;
use App\Models\InvoiceProduct;
use Illuminate\Support\Facades\Auth;

class Index extends Component
{
    public $name;
    public $products;
    public $product;
    public $product_id;

    public function mount(){
        $this->products = InvoiceProduct::orderBy('name','asc')->get();
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
        try{
    
            $product = new InvoiceProduct;
            $product->user_id = Auth::user()->id;
            $product->name = $this->name;
            $product->save(); 
    
            $this->dispatchBrowserEvent('hide-invoice_productModal');
            $this->resetInputFields();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Product Created Successfully!!"
            ]);
    
            }
                catch(\Exception $e){
                // Set Flash Message
                $this->dispatchBrowserEvent('alert',[
                    'type'=>'error',
                    'message'=>"Something went wrong while creating product!!"
                ]);
             }
        }

        public function edit($id){
            $this->product_id = $id;
            $this->product = InvoiceProduct::find($id);
            $this->name = $this->product->name;
            $this->dispatchBrowserEvent('show-invoice_productEditModal');

        }

        public function update(){
            try{
        
                $product = InvoiceProduct::find($this->product_id);
                $product->name = $this->name;
                $product->update(); 
        
                $this->dispatchBrowserEvent('hide-invoice_productEditModal');
                $this->resetInputFields();
                $this->dispatchBrowserEvent('alert',[
                    'type'=>'success',
                    'message'=>"Product Updated Successfully!!"
                ]);
        
                }
                    catch(\Exception $e){
                    // Set Flash Message
                    $this->dispatchBrowserEvent('alert',[
                        'type'=>'error',
                        'message'=>"Something went wrong while updating product!!"
                    ]);
                 }
            }
    public function render()
    {
        $this->products = InvoiceProduct::orderBy('name','asc')->get();
        return view('livewire.invoice-products.index',[
            'products' =>   $this->products
        ]);
    }
}
