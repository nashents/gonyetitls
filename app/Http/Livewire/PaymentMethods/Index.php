<?php

namespace App\Http\Livewire\PaymentMethods;

use Livewire\Component;
use App\Models\PaymentMethod;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class Index extends Component
{
    public $payment_methods;
    public $name;
   

    public $payment_method_id;
    public $user_id;

    public function mount(){
        $this->payment_methods = PaymentMethod::orderBy('name','asc')->get();
    }
    private function resetInputFields(){
     
        $this->name = "";
       
    }

    public function updated($value){
        $this->validateOnly($value);
    }
    protected $rules = [
        'name' => 'required|unique:payment_methods,name,NULL,id,deleted_at,NULL|string|min:2',
       
    ];

    public function store(){
        try{
        $payment_method = new PaymentMethod;
        $payment_method->user_id = Auth::user()->id;
        $payment_method->name = $this->name;
        $payment_method->save();
        $this->dispatchBrowserEvent('hide-payment_methodModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Payment Method Created Successfully!!"
        ]);

        }
            catch(\Exception $e){
            // Set Flash Message
            $this->dispatchBrowserEvent('alert',[
                'type'=>'error',
                'message'=>"Something went wrong while creating payment method!!"
            ]);
         }

    }

    public function edit($id){
    $payment_method = PaymentMethod::find($id);
    $this->user_id = $payment_method->user_id;
    $this->name = $payment_method->name;
    $this->payment_method_id = $payment_method->id;
    $this->dispatchBrowserEvent('show-payment_methodEditModal');

    }



    public function update()
    {
        if ($this->payment_method_id) {
            try {
            $payment_method = PaymentMethod::find($this->payment_method_id);
            $payment_method->update([
                'user_id' => Auth::user()->id,
                'name' => $this->name,
            ]);

            $this->dispatchBrowserEvent('hide-payment_methodEditModal');
            $this->resetInputFields();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Payment Method Updated Successfully!!"
            ]);
            }
                catch(\Exception $e){
                // Set Flash Message
                $this->dispatchBrowserEvent('alert',[
                    'type'=>'error',
                    'message'=>"Something went wrong while updating payment method!!"
                ]);
             }

        }
    }

    public function render()
    {
        $this->payment_methods = PaymentMethod::orderBy('name','asc')->get();
        return view('livewire.payment-methods.index',[
            'payment_methods' => $this->payment_methods
        ]);
    }
}
