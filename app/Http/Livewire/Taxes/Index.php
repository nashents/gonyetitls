<?php

namespace App\Http\Livewire\Taxes;

use App\Models\Account;
use App\Models\Tax;
use App\Services\Sage\SageIntegration;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
     use WithPagination;
     use \App\Http\Livewire\Concerns\PullsFromSage;
    protected $paginationTheme = 'bootstrap';
    public $search;
    protected $queryString = ['search'];
    public $tax_id;
    public $accounts;
    public $account_id;
    public $tax;
    public $name;
    public $abbreviation;
    public $hs_code;
    public $rate;
    public $description;
    public $category;
  

    public function remove($i)
    {
        unset($this->inputs[$i]);
    }

    public function mount(){
      $this->accounts = Account::orderBy('name','asc')->get();
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
        $this->category = '';
        $this->name = '';
        $this->description = '';
        $this->rate = '';
        $this->tax_id = "" ;
        $this->hs_code = "" ;
    }



    public function store(){
    
        $account = new Tax;

        $account->user_id = Auth::user()->id;
        $account->account_id = $this->account_id;
        $account->category = $this->category;
        $account->name = $this->name;
        $account->abbreviation = $this->abbreviation;
        $account->rate = $this->rate;
        $account->description = $this->description;
        $account->hs_code = $this->hs_code;
        $account->save();

        $this->dispatchBrowserEvent('hide-taxModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'abbreviation'=>'success',
            'message'=>"Tax Created Successfully!!"
        ]);

     
    }

    public function edit($id){
        
        $tax = Tax::find($id);
        $this->name = $tax->name;
        $this->category = $tax->category;
        $this->abbreviation = $tax->abbreviation;
        $this->description = $tax->description;
        $this->hs_code = $tax->hs_code;
        $this->rate = $tax->rate;
        $this->tax_id = $tax->id;
        $this->account_id = $tax->account_id;

        $this->dispatchBrowserEvent('show-taxEditModal');

    }


    public function update()
    {
        if ($this->tax_id) {
        
            $account = Tax::find($this->tax_id);
            $account->category = $this->category;
            $account->name = $this->name;
            $account->account_id = $this->account_id;
            $account->abbreviation = $this->abbreviation;
            $account->rate = $this->rate;
            $account->description = $this->description;
            $account->hs_code = $this->hs_code;
            $account->update();

            $this->dispatchBrowserEvent('hide-taxEditModal');
            $this->resetInputFields();
            $this->dispatchBrowserEvent('alert',[
                'abbreviation'=>'success',
                'message'=>"Tax Updated Successfully!!"
            ]);

        }
    }


    /** Sage integration gate — controls the "Pull from Sage" button. */
    public function getSageEnabledProperty()
    {
        return SageIntegration::enabledForUser();
    }

    /** Queue an import of Sage's item tax groups into the taxes module. */
    public function pullFromSage()
    {
        $this->dispatchSagePull('tax', 'tax groups');
    }

    public function render()
    {

        $query = Tax::orderBy('name','asc')->paginate(10);
        return view('livewire.taxes.index',[
            'taxes'=>  $query
        ]);
    }
}
