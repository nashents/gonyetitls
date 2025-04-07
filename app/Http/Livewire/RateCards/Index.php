<?php

namespace App\Http\Livewire\RateCards;

use Livewire\Component;
use App\Models\Currency;
use App\Models\RateCard;

class Index extends Component
{

    public $rate_cards;
    public $rate_card;
    public $rate_card_id;
    public $currencies;
    public $currency;
    public $currency_id;
    public $rate;
    public $days;
    public $load_status;

    public function mount(){
        $this->rate_cards = RateCard::all();
        $this->currencies = Currency::all();
    }

    public function updated($value){
        $this->validateOnly($value);
    }
    protected $rules = [
        'rate' => 'required',
        'currency_id' => 'required',
        'load_status' => 'required',
        'days' => 'required',
    ];

    private function resetInputFields(){
        $this->rate = '';
        $this->currency_id = '';
        $this->load_status = '';
        $this->days = '';
    }

    public function store(){

        $rate_card = new RateCard;
        $rate_card->currency_id = $this->currency_id;
        $rate_card->rate = $this->rate;
        $rate_card->days = $this->days;
        $rate_card->load_status = $this->load_status;
        $rate_card->save();

        $this->dispatchBrowserEvent('hide-rate_cardModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Rate Created Successfully!!"
        ]);
    }

    public function edit($id){

        $rate_card = RateCard::find($id);
        $this->load_status = $rate_card->load_status;
        $this->currency_id = $rate_card->currency_id;
        $this->rate = $rate_card->rate;
        $this->days = $rate_card->days;
        $this->rate_card_id = $rate_card->id;

        $this->dispatchBrowserEvent('show-rate_cardEditModal');
    }
  
    public function update(){
        $rate_card = RateCard::find($this->rate_card_id);
        $rate_card->currency_id = $this->currency_id;
        $rate_card->rate = $this->rate;
        $rate_card->days = $this->days;
        $rate_card->load_status = $this->load_status;
        $rate_card->update();

        $this->dispatchBrowserEvent('hide-rate_cardEditModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Rate Updated Successfully!!"
        ]);
    }

    public function render()
    {
        $this->rate_cards = RateCard::all();
        return view('livewire.rate-cards.index',[
            'rate_cards' => $this->rate_cards
        ]);
    }
}
