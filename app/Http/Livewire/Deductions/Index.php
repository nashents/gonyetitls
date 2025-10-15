<?php

namespace App\Http\Livewire\Deductions;

use Livewire\Component;
use App\Models\Currency;
use App\Models\Deduction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class Index extends Component
{
    public $deductions;
    public $calculate_by = "currency";
    public $calculate_on;
    public $description;
    public $name;
    public $amount;
    public $percentage;
    public $status;
    public $deduction_id;
    public $user_id;
    public $currencies;
    public $currency_id;

    public function mount(){
        $this->deductions = Deduction::orderBy('name','asc')->get();
        $this->currencies = Currency::orderBy('name','asc')->get();
    }

    public function updated($value){
        $this->validateOnly($value);
    }
    protected $rules = [
        'calculate_by' => 'required',
        'name' => 'required|unique:deductions,name,NULL,id,deleted_at,NULL|string|min:2',
    ];

    private function resetInputFields(){
        $this->calculate_by = "currency";
        $this->calculate_on = '';
        $this->name = '';
        $this->currency_id = '';
        $this->amount = '';
        $this->percentage = '';
        $this->description = '';
    }

    public function store(){
        try{
        $deduction = new Deduction;
        $deduction->user_id = Auth::user()->id;
        $deduction->name = $this->name;
        $deduction->currency_id = $this->currency_id;
        $deduction->calculate_by = $this->calculate_by;
        $deduction->calculate_on = $this->calculate_on;
        if ($this->calculate_by == "currency") {
            $deduction->amount = $this->amount;
            $deduction->percentage = Null;
        }elseif ($this->calculate_by == "percentage") {
            $deduction->amount = Null;
            $deduction->percentage =  $this->percentage;
        }
       
        $deduction->description = $this->description;
        $deduction->status =1;
        $deduction->save();

        $this->dispatchBrowserEvent('hide-deductionModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Deduction Created Successfully!!"
        ]);


        }
        catch(\Exception $e){
        // Set Flash Message
        $this->dispatchBrowserEvent('alert',[
            'type'=>'error',
            'message'=>"Something goes wrong while creating deduction!!"
        ]);
    }
    }

    public function edit($id){
    $deduction = Deduction::find($id);
    $this->user_id = $deduction->user_id;
    $this->name = $deduction->name;
    $this->currency_id = $deduction->currency_id;
    $this->calculate_on = $deduction->calculate_on;
    $this->calculate_by = $deduction->calculate_by;
    $this->amount = $deduction->amount;
    $this->percentage = $deduction->percentage;
    $this->description = $deduction->description;
    $this->deduction_id = $deduction->id;
    $this->status = $deduction->status;
    $this->dispatchBrowserEvent('show-deductionEditModal');

    }


    public function update()
    {
        if ($this->deduction_id) {
            try{
            $deduction = Deduction::find($this->deduction_id);
            $deduction->user_id = Auth::user()->id;
            $deduction->name = $this->name;
            $deduction->calculate_by = $this->calculate_by;
            $deduction->calculate_on = $this->calculate_on;
            $deduction->currency_id = $this->currency_id;
            if ($this->calculate_by == "currency") {
                $deduction->amount = $this->amount;
                $deduction->percentage = Null;
            }elseif ($this->calculate_by == "percentage") {
                $deduction->amount = Null;
                $deduction->percentage =  $this->percentage;
            }
            $deduction->description = $this->description;
            $deduction->status = $this->status;
            $deduction->update();

            $this->dispatchBrowserEvent('hide-deductionEditModal');
            $this->resetInputFields();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Deduction Updated Successfully!!"
            ]);


            // return redirect()->route('deductions.index');
            }
            catch(\Exception $e){
            $this->dispatchBrowserEvent('hide-deductionEditModal');
            $this->dispatchBrowserEvent('alert',[
                'type'=>'error',
                'message'=>"Something goes wrong while creating deduction!!"
            ]);
          }
        }
    }


    public function render()
    {
        $this->deductions = Deduction::orderBy('name','asc')->get();
        return view('livewire.deductions.index',[
            'deductions'=>   $this->deductions
        ]);
    }
}
