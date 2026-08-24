<?php

namespace App\Http\Livewire\Allowances;

use Livewire\Component;
use App\Models\Account;
use App\Models\Currency;
use App\Models\Allowance;
use App\Models\Tax;
use Illuminate\Support\Facades\Auth;

class Index extends Component
{
    public $allowances;
    public $calculate_by = "currency";
    public $calculate_on;
    public $description;
    public $name;
    public $amount;
    public $percentage;
    public $status;
    public $allowance_id;
    public $currencies;
    public $currency_id;
    public $user_id;
    public $taxes;
    public $tax_id;
    public $type;
    public $accounts;
    public $account_id;

    public function mount(){
        $this->allowances = Allowance::orderBy('name','asc')->get();
        $this-> currencies = Currency::orderBy('name','asc')->get();
        $this->taxes = Tax::whereHas('account', function ($query) {
            return $query->where('name','Value Added Tax');
        })->orderBy('name','asc')->get();
        $this->accounts = Account::whereHas('account_type', function ($query) {
            $query->where('name', 'Payroll Expense');
        })->orderBy('name','asc')->get();
    }

    public function updated($value){
        $this->validateOnly($value);
    }
    protected $rules = [
        'calculate_by' => 'required',
        'name' => 'required|unique:allowances,name,NULL,id,deleted_at,NULL|string|min:2',
        'account_id' => 'required',
    ];

    private function resetInputFields(){
        $this->calculate_by = "currency";
        $this->calculate_on = '';
        $this->name = '';
        $this->amount = '';
        $this->percentage = '';
        $this->currency_id = '';
        $this->description = '';
        $this->tax_id = '';
        $this->type = '';
        $this->account_id = '';
    }

    public function store(){
        try{
        $allowance = new Allowance;
        $allowance->user_id = Auth::user()->id;
        $allowance->name = $this->name;
        $allowance->calculate_by = $this->calculate_by;
        $allowance->calculate_on = $this->calculate_on;
        $allowance->currency_id = $this->currency_id;
        if ($this->calculate_by == "currency") {
            $allowance->amount = $this->amount;
            $allowance->percentage = Null;
        }elseif ($this->calculate_by == "percentage") {
            $allowance->amount = Null;
            $allowance->percentage =  $this->percentage;
        }

        $allowance->description = $this->description;
        $allowance->tax_id = $this->tax_id;
        $allowance->type = $this->type;
        $allowance->account_id = $this->account_id;
        $allowance->status =1;
        $allowance->save();

        $this->dispatchBrowserEvent('hide-allowanceModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Allowance Created Successfully!!"
        ]);


        }
        catch(\Exception $e){
        // Set Flash Message
        $this->dispatchBrowserEvent('alert',[
            'type'=>'error',
            'message'=>"Something goes wrong while creating allowance!!"
        ]);
    }
    }

    public function edit($id){
    $allowance = Allowance::find($id);
    $this->user_id = $allowance->user_id;
    $this->name = $allowance->name;
    $this->calculate_on = $allowance->calculate_on;
    $this->calculate_by = $allowance->calculate_by;
    $this->amount = $allowance->amount;
    $this->currency_id = $allowance->currency_id;
    $this->percentage = $allowance->percentage;
    $this->description = $allowance->description;
    $this->tax_id = $allowance->tax_id;
    $this->type = $allowance->type;
    $this->account_id = $allowance->account_id;
    $this->allowance_id = $allowance->id;
    $this->status = $allowance->status;
    $this->dispatchBrowserEvent('show-allowanceEditModal');

    }


    public function update()
    {
        if ($this->allowance_id) {
            try{
            $allowance = Allowance::find($this->allowance_id);
            $allowance->user_id = Auth::user()->id;
            $allowance->name = $this->name;
            $allowance->calculate_by = $this->calculate_by;
            $allowance->currency_id = $this->currency_id;
            $allowance->calculate_on = $this->calculate_on;
            if ($this->calculate_by == "currency") {
                $allowance->amount = $this->amount;
                $allowance->percentage = Null;
            }elseif ($this->calculate_by == "percentage") {
                $allowance->amount = Null;
                $allowance->percentage =  $this->percentage;
            }
            $allowance->description = $this->description;
            $allowance->tax_id = $this->tax_id;
            $allowance->type = $this->type;
            $allowance->account_id = $this->account_id;
            $allowance->status = $this->status;
            $allowance->update();

            $this->dispatchBrowserEvent('hide-allowanceEditModal');
            $this->resetInputFields();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Allowance Updated Successfully!!"
            ]);


            // return redirect()->route('allowances.index');
            }
            catch(\Exception $e){
            $this->dispatchBrowserEvent('hide-allowanceEditModal');
            $this->dispatchBrowserEvent('alert',[
                'type'=>'error',
                'message'=>"Something goes wrong while creating allowance!!"
            ]);
          }
        }
    }


    public function render()
    {
        $this->allowances = Allowance::with('tax','account')->orderBy('name','asc')->get();
        return view('livewire.allowances.index',[
            'allowances'=>   $this->allowances
        ]);
    }
}
