<?php

namespace App\Http\Livewire\Taxes;

use App\Models\Tax;
use App\Models\Account;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class Index extends Component
{
   
    public $taxes;
    public $tax_id;
    public $account_types;
    public $account_type_id;
    public $tax;
    public $name;
    public $abbreviation;
    public $tax_number;
    public $rate;
    public $description;
    public $compound_tax = False;
    public $show_tax_number = False;
    public $tax_recoverable = True;
   
    public $user_id;
  
  


    public function remove($i)
    {
        unset($this->inputs[$i]);
    }

    public function mount(){
        $this->taxes = Account::whereHas('account_type', function ($query) {
            return $query->where('name','Sales Taxes');
        })->get();
    }

    public function updated($value){
        $this->validateOnly($value);
    }
    protected $rules = [
        'name' => 'required|unique:accounts,name,NULL,id,deleted_at,NULL|string|min:2',
        'abbreviation' => 'required|unique:accounts,abbreviation,NULL,id,deleted_at,NULL|string|min:2',
    ];

    private function resetInputFields(){
        $this->abbreviation = '';
        $this->compound_tax = '';
        $this->name = '';
        $this->show_tax_number = '';
        $this->tax_number = '';
        $this->tax_recoverable = '';
        $this->description = '';
        $this->rate = '';
        $this->tax_id = "" ;
    }



    public function store(){
        // try{
        $account_type = AccountType::where('name','Sales Taxes')->first();
        $account = new Account;

        $account->user_id = Auth::user()->id;
        $account->account_type_id = $account_type ? $account_type->id : Null;
        $account->name = $this->name;
        $account->abbreviation = $this->abbreviation;
        $account->tax_number = $this->tax_number;
        $account->rate = $this->rate;
        $account->tax_recoverable = $this->tax_recoverable;
        $account->show_tax_number = $this->show_tax_number;
        $account->description = $this->description;
        $account->compound_tax = $this->compound_tax;
        $account->save();

        $this->dispatchBrowserEvent('hide-taxModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'abbreviation'=>'success',
            'message'=>"Tax Created Successfully!!"
        ]);

        // return redirect()->route('taxes.index');

    //     }
    //     catch(\Exception $e){
    //     // Set Flash Message
    //     $this->dispatchBrowserEvent('alert',[
    //         'abbreviation'=>'error',
    //         'message'=>"Something goes wrong while creating tax!!"
    //     ]);
    // }
    }

    public function edit($id){
        
    $account = Account::find($id);
    $this->user_id = $account->user_id;
    $this->account_type_id = $account->account_type_id;
    $this->name = $account->name;
    $this->abbreviation = $account->abbreviation;
    $this->description = $account->description;
    $this->show_tax_number = $account->show_tax_number;
    $this->tax_recoverable = $account->tax_recoverable;
    $this->compound_tax = $account->compound_tax;
    $this->tax_number = $account->tax_number;
    $this->rate = $account->rate;
    $this->tax_id = $account->id;

    $this->dispatchBrowserEvent('show-taxEditModal');

    }


    public function update()
    {
        if ($this->tax_id) {
            try{
            $account = Account::find($this->tax_id);
            $account->name = $this->name;
            $account->account_type_id = $this->account_type_id;
            $account->abbreviation = $this->abbreviation;
            $account->tax_number = $this->tax_number;
            $account->rate = $this->rate;
            $account->tax_recoverable = $this->tax_recoverable;
            $account->show_tax_number = $this->show_tax_number;
            $account->description = $this->description;
            $account->compound_tax = $this->compound_tax;
            $account->update();

            $this->dispatchBrowserEvent('hide-taxEditModal');
            $this->resetInputFields();
            $this->dispatchBrowserEvent('alert',[
                'abbreviation'=>'success',
                'message'=>"Tax Updated Successfully!!"
            ]);


            // return redirect()->route('taxes.index');
            }
            catch(\Exception $e){
            $this->dispatchBrowserEvent('hide-taxEditModal');
            $this->dispatchBrowserEvent('alert',[
                'abbreviation'=>'error',
                'message'=>"Something goes wrong while creating Tax!!"
            ]);
          }
        }
    }


    public function render()
    {
        $this->taxes = Account::whereHas('account_type', function ($query) {
            return $query->where('name','Sales Taxes');
        })->get();
        return view('livewire.taxes.index',[
            'taxes'=>   $this->taxes
        ]);
    }
}
