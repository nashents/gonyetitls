<?php

namespace App\Http\Livewire\Earnings;

use App\Models\Earning;
use Livewire\Component;
use App\Models\Currency;
use Illuminate\Support\Facades\Auth;

class Index extends Component
{
    public $earnings;
    public $currencies;
    public $currency_id;
    public $description;
    public $name;
    public $status;
    public $earning_id;
    public $user_id;

    public function mount(){
        $this->earnings = Earning::orderBy('name','asc')->get();
        $this->currencies = Currency::orderBy('name','asc')->get();
    }

    public function updated($value){
        $this->validateOnly($value);
    }
    protected $rules = [
        'name' => 'required|unique:earnings,name,NULL,id,deleted_at,NULL|string|min:2',
    ];

    private function resetInputFields(){
        $this->currency_id = '';
        $this->name = '';
        $this->description = '';
    }

    public function store(){
       
        $earning = new Earning;
        $earning->user_id = Auth::user()->id;
        $earning->name = $this->name;
        $earning->currency_id = $this->currency_id;
        $earning->description = $this->description;
        $earning->status =1;
        $earning->save();

        $this->dispatchBrowserEvent('hide-earningModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Earning Created Successfully!!"
        ]);

    }

    public function edit($id){
    $earning = Earning::find($id);
    $this->name = $earning->name;
    $this->currency_id = $earning->currency_id;
    $this->description = $earning->description;
    $this->earning_id = $earning->id;
    $this->status = $earning->status;
    $this->dispatchBrowserEvent('show-earningEditModal');

    }


    public function update()
    {
        if ($this->earning_id) {
           
            $earning = Earning::find($this->earning_id);
            $earning->user_id = Auth::user()->id;
            $earning->name = $this->name;
            $earning->currency_id = $this->currency_id;
            $earning->description = $this->description;
            $earning->status = $this->status;
            $earning->update();

            $this->dispatchBrowserEvent('hide-earningEditModal');
            $this->resetInputFields();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Earning Updated Successfully!!"
            ]);


            // return redirect()->route('earnings.index');
          
        }
    }


    public function render()
    {
        $this->earnings = Earning::orderBy('name','asc')->get();
        return view('livewire.earnings.index',[
            'earnings'=>   $this->earnings
        ]);
    }
}
