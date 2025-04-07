<?php

namespace App\Http\Livewire\Rates;

use App\Models\Rate;
use App\Models\Cargo;
use Livewire\Component;
use App\Models\Currency;
use App\Models\Destination;
use App\Models\LoadingPoint;
use Maatwebsite\Excel\Excel;
use App\Models\OffloadingPoint;
use Illuminate\Support\Facades\Auth;

class Index extends Component
{
    public $rates;
    public $destinations;
    public $destination_id;
    public $from;
    public $to;
    public $offloading_points;
    public $offloading_point_id;
    public $loading_points;
    public $loading_point_id;
    public $currencies;
    public $currency_id;
    public $rate;
    public $weight;
    public $distance;
    public $freight_calculation;
    public $cargos;
    public $selectedCargo;
    public $cargo_type;
    public $litreage;
    public $category;



    public $rate_id;
    public $user_id;

    public function mount(){
        $this->cargo_type = "Solid";
        $this->rates = Rate::all();
        $this->destinations = Destination::all();
        $this->currencies = Currency::all();
        $this->cargos = Cargo::all();
        $this->loading_points = LoadingPoint::all();
        $this->offloading_points = OffloadingPoint::all();
    }
    private function resetInputFields(){
        $this->from = '';
        $this->to = '';
        $this->rate = '';
        $this->weight = '';
        $this->category = '';
        $this->selectedCargo = '';
        $this->freight_calculation = '';
        $this->distance = '';
        $this->litreage = '';
        $this->currency_id = '';
        $this->offloading_point_id = '';
        $this->loading_point_id = '';
    }

    public function exportratesCSV(Excel $excel){

        return $excel->download(new RatesExport, 'rates.csv', Excel::CSV);
    }
    public function exportratesPDF(Excel $excel){

        return $excel->download(new RatesExport, 'rates.pdf', Excel::DOMPDF);
    }
    public function exportratesExcel(Excel $excel){

        return $excel->download(new RatesExport, 'rates.xlsx');
    }
    public function updated($value){
        $this->validateOnly($value);
    }
    protected $rules = [
        'category' => 'required',
        'from' => 'required',
        'to' => 'required',
        'rate' => 'required',
        'currency_id' => 'required',
    ];

    public function updatedSelectedCargo($id)
    {
            if (!is_null($id)) {
                $this->cargo = Cargo::find($id);
                $this->cargo_type = $this->cargo->type;
            }
    }

    public function store(){
        // try{
        $rate = new Rate;
        $rate->user_id = Auth::user()->id;
        $rate->from = $this->from;
        $rate->to = $this->to;
        $rate->loading_point_id = $this->loading_point_id;
        $rate->offloading_point_id = $this->offloading_point_id;
        $rate->currency_id = $this->currency_id;
        $rate->cargo_id = $this->selectedCargo;
        $rate->rate = $this->rate;
        $rate->distance = $this->distance;
        $rate->freight_calculation = $this->freight_calculation;
        $rate->category = $this->category;
        $rate->weight = $this->weight;
        $rate->litreage = $this->litreage;
        $rate->save();

        $this->dispatchBrowserEvent('hide-rateModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Rate Created Successfully!!"
        ]);

    // }
    // catch(\Exception $e){
    // // Set Flash Message
    // $this->dispatchBrowserEvent('alert',[
    //     'type'=>'error',
    //     'message'=>"Something goes wrong while creating rate!!"
    // ]);
    //  }
    }

    public function edit($id){
    $rate = Rate::find($id);
    $this->from = $rate->from;
    $this->to = $rate->to;
    $this->loading_point_id = $rate->loading_point_id;
    $this->offloading_point_id = $rate->offloading_point_id;
    $this->currency_id = $rate->currency_id;
    $this->freight_calculation = $rate->freight_calculation;
    $this->distance = $rate->distance;
    $this->selectedCargo = $rate->cargo_id;
    $this->rate = $rate->rate;
    $this->weight = $rate->weight;
    $this->litreage = $rate->litreage;
    $this->category = $rate->category;
    $this->rate_id = $rate->id;
    $this->dispatchBrowserEvent('show-rateEditModal');

    }



    public function update()
    {
        if ($this->rate_id) {
            try{
            $rate = Rate::find($this->rate_id);
            $rate->user_id = Auth::user()->id;
            $rate->from = $this->from;
            $rate->to = $this->to;
            $rate->loading_point_id = $this->loading_point_id;
            $rate->offloading_point_id = $this->offloading_point_id;
            $rate->currency_id = $this->currency_id;
            $rate->cargo_id = $this->selectedCargo;
            $rate->freight_calculation = $this->freight_calculation;
            $rate->distance = $this->distance;
            $rate->rate = $this->rate;
            $rate->weight = $this->weight;
            $rate->category = $this->category;
            $rate->litreage = $this->litreage;
            $rate->update();
            $this->dispatchBrowserEvent('hide-rateEditModal');
            $this->resetInputFields();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Rate Updated Successfully!!"
            ]);

        }
        catch(\Exception $e){
        // Set Flash Message
        $this->dispatchBrowserEvent('alert',[
            'type'=>'error',
            'message'=>"Something goes wrong while creating rate!!"
        ]);
         }

        }
    }

    public function render()
    {
        $this->rates = Rate::latest()->get();
        $this->destinations = Destination::all();
        $this->currencies = Currency::all();
        $this->cargos = Cargo::all();
        $this->loading_points = LoadingPoint::all();
        $this->offloading_points = OffloadingPoint::all();
        return view('livewire.rates.index',[
            'rates' => $this->rates,
            'destinations' => $this->destinations,
            'currencies' => $this->currencies,
            'cargos' => $this->cargos,
            'loading_points' => $this->loading_points,
            'offloading_points' => $this->offloading_points,
        ]);
    }
}
