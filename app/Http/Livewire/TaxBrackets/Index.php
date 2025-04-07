<?php

namespace App\Http\Livewire\TaxBrackets;

use Livewire\Component;
use App\Models\Currency;
use App\Models\TaxBracket;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';
    public $search;
    protected $queryString = ['search'];

    private $tax_brackets;
    public $tax_bracket;
    public $tax_bracket_id;
    public $currencies;
    public $currency_id;
    public $lower_band;
    public $upper_band;
    public $percentage;
    public $rate;
    public $user_id;


    public function mount(){
        $this->resetPage();
        $this->currencies = Currency::orderBy('name','asc')->get();
    }

   

    public function updated($value){
        $this->validateOnly($value);
    }
    protected $rules = [
        'frequency' => 'required',
    ];

    private function resetInputFields(){
        $this->frequency = '';
        $this->lower_band= '';
        $this->upper_band= '';
        $this->percentage= '';
        $this->rate = '';
        $this->currency_id = '';
    }

    public function store(){
        $tax_bracket = new TaxBracket;
        $tax_bracket->user_id = Auth::user()->id;
        $tax_bracket->currency_id = $this->currency_id;
        $tax_bracket->frequency = $this->frequency;
        $tax_bracket->lower_band = $this->lower_band;
        $tax_bracket->upper_band = $this->upper_band;
        $tax_bracket->percentage = $this->percentage;
        $tax_bracket->rate = $this->rate;
        $tax_bracket->save();
      
        $this->dispatchBrowserEvent('hide-tax_bracketModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"TaxBracket(s) Added Successfully!!"
        ]);
       
    }

    public function edit($id){
    $tax_bracket = TaxBracket::find($id);
    $this->user_id = $tax_bracket->user_id;
    $this->frequency = $tax_bracket->frequency;
    $this->currency_id = $tax_bracket->currency_id;
    $this->lower_band = $tax_bracket->lower_band;
    $this->upper_band = $tax_bracket->upper_band;
    $this->percentage = $tax_bracket->percentage;
    $this->rate = $tax_bracket->rate;
    $this->tax_bracket_id = $tax_bracket->id;
    $this->dispatchBrowserEvent('show-tax_bracketEditModal');

    }

    public function update()
    {
        if ($this->tax_bracket_id) {
            $tax_bracket =  TaxBracket::find($this->tax_bracket_id);
            $tax_bracket->currency_id = $this->currency_id;
            $tax_bracket->frequency = $this->frequency;
            $tax_bracket->lower_band = $this->lower_band;
            $tax_bracket->upper_band = $this->upper_band;
            $tax_bracket->percentage = $this->percentage;
            $tax_bracket->rate = $this->rate;
            $tax_bracket->update();

        $this->dispatchBrowserEvent('hide-tax_bracketEditModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Tax Bracket Updated Successfully!!"
        ]);

        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
       

        if (isset($this->search)) {
            return view('livewire.tax-brackets.index',[
                'tax_brackets' => TaxBracket::with('currency')->paginate(10),
              
            ]);
        }else {
            return view('livewire.tax-brackets.index',[
                'tax_brackets' => TaxBracket::with('currency')->paginate(10),
               
            ]);
        }

      
    }
}
