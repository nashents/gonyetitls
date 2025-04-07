<?php

namespace App\Http\Livewire\Contracts;

use App\Models\Vendor;
use Livewire\Component;
use App\Models\Contract;
use App\Models\Customer;
use App\Models\Consignee;
use Illuminate\Support\Facades\Auth;

class Index extends Component
{

    public $category;
    public $customer;
    public $customer_id;
    public $vendors;
    public $vendor_id;
    public $user_id;
    public $contracts;
    public $contract_id;
    public $start_date;
    public $end_date;
    public $duration;
    public $status;
    public $comments;



    private function resetInputFields(){
        $this->start_date = "" ;
        $this->end_date = "";
        $this->comments = "";
        $this->duration = "";
        $this->status = "";
    }


    public function mount($id,$category){

        $this->category = $category;

    if ($this->category == "customer") {
        $this->customer_id = $id;
        $this->customer = Customer::find($id);
        $this->contracts = Contract::where('category', $this->category)
        ->where('customer_id', $this->customer->id)->latest()->get();
    }elseif($this->category == "vendor"){
        $this->vendor_id = $id;
        $this->vendor = Vendor::find($id);
        $this->contracts = Contract::where('category', $this->category)
        ->where('vendor_id', $this->vendor->id)->latest()->get();
    }
   
    }

    public function store(){
        // try{

            $contract = new Contract;
            $contract->user_id = Auth::user()->id;
            $contract->category = $this->category;
            if (isset($this->customer_id)) {
                $contract->customer_id = $this->customer_id;
            }elseif (isset($this->vendor_id)) {
                $contract->vendor_id = $this->vendor_id;
            }
            $contract->start_date = $this->start_date;
            $contract->end_date = $this->end_date;
            $contract->duration = $this->duration;
            $contract->comments = $this->comments;
            $contract->category = $this->category;
            $contract->status = 1;
            $contract->save();

            $this->dispatchBrowserEvent('hide-contractModal');
            $this->resetInputFields();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Contract(s) Uploaded Successfully!!"
            ]);

        // }catch(\Exception $e){
        //     // Set Flash Message
        //     $this->dispatchBrowserEvent('alert',[
        //         'type'=>'error',
        //         'message'=>"Something went wrong while uploading contract(s)!!"
        //     ]);
        // }
    }

    public function edit($id){

        $contract = Contract::find($id);
        $this->user_id = $contract->user_id;
        $this->customer_id = $contract->customer_id;
        $this->vendor_id = $contract->vendor_id;
        $this->start_date = $contract->start_date;
        $this->end_date = $contract->end_date;
        $this->duration = $contract->duration;
        $this->category = $contract->category;
        $this->comments = $contract->comments;
        $this->status = $contract->status;
        $this->contract_id = $contract->id;

        $this->dispatchBrowserEvent('show-contractEditModal');

        }

        public function update()
        {
            if ($this->contract_id) {
                try{
                  
                $contract = Contract::find($this->contract_id);   

                if (isset($this->customer_id)) {
                    $contract->customer_id = $this->customer_id;
                }elseif (isset($this->vendor_id)) {
                    $contract->vendor_id = $this->vendor_id;
                }
        
                $contract->start_date = $this->start_date;
                $contract->end_date = $this->end_date;
                $contract->duration = $this->duration;
                $contract->comments = $this->comments;
                $contract->category = $this->category;
                $contract->status = $this->status;
                $contract->update();

                $this->dispatchBrowserEvent('hide-contractEditModal');
                $this->resetInputFields();
                $this->dispatchBrowserEvent('alert',[
                    'type'=>'success',
                    'message'=>"Contract Updated Successfully!!"
                ]);
            }catch(\Exception $e){
                // Set Flash Message
                $this->dispatchBrowserEvent('alert',[
                    'type'=>'error',
                    'message'=>"Something went wrong while updating contract(s)!!"
                ]);
            }

            }
        }


    public function render()
    {
        if ($this->category == "customer") {
            $this->contracts = Contract::where('category', $this->category)
            ->where('customer_id', $this->customer->id)->latest()->get();
        }elseif ($this->category == "vendor") {
            $this->contracts = Contract::where('category', $this->category)
            ->where('vendor_id', $this->vendor->id)->latest()->get();
        }
     
        return view('livewire.contracts.index',[
            'contracts'=> $this->contracts
        ]);
    }
}
