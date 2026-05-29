<?php

namespace App\Http\Livewire\Trips;

use App\Models\Destination;
use App\Models\LoadingPoint;
use App\Models\TripOrigin;
use App\Models\UnitsOfMeasure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Origins extends Component
{
    public $trip;
    public $trip_id;
    public $cargo;
    public $trip_origins;
    public $destinations;
    public $destination_id;
    public $loading_points;
    public $loading_point_id;
    public $weight;
    public $rate;
    public $freight;
    public $old_weight;
    public $quantity;
    public $old_quantity;
    public $litreage;
    public $old_litreage;
    public $loading_date;
    public $litreage_at_20;
    public $old_litreage_at_20;
    public $units_of_measures;
    public $units_of_measure_id;
    public $trip_destination_id;
    public $trip_destination;


    public $inputs = [];
    public $i = 1;
    public $n = 1;

    public function add($i)
    {
        $i = $i + 1;
        $this->i = $i;
        array_push($this->inputs ,$i);
    }

    public function remove($i)
    {
        unset($this->inputs[$i]);
    }

    private function resetInputFields(){
        $this->destination_id = [] ;
        $this->loading_point_id = [];
        $this->units_of_measure_id = [];
        $this->weight = [];
        $this->quantity = [];
        $this->litreage = [];
        $this->litreage_at_20 = [];
        $this->loading_date = [];

    }

    public function updated($value){
        $this->validateOnly($value);
    }

    
    protected $rules = [
        'destination_id' => 'required',
        'loading_point_id' => 'required',
        'loading_date' => 'required',
    ];

    public function mount($trip){
        $this->trip = $trip;
        $this->trip_id = $trip->id;
        $this->cargo = $this->trip->cargo;
        $this->trip_origins = $this->trip->trip_origins;
        $this->destinations = Destination::orderBy('city','asc')->get();
        $this->units_of_measures = UnitsOfMeasure::orderBy('name','asc')->get();
        $this->loading_points = LoadingPoint::orderBy('name','asc')->get();
    }

    public function store(){

        $this->validate();

        DB::transaction(function () {
        
            if (isset($this->destination_id)) {

                foreach ($this->destination_id as $key => $value) {

                    $trip_origin = new TripOrigin;
                    $trip_origin->user_id = Auth::user()->id;
                    $trip_origin->trip_id = $this->trip_id;
                    if (isset($this->loading_date[$key])) {
                        $trip_origin->loading_date = $this->loading_date[$key];
                    }
                    if (isset($this->loading_point_id[$key])) {
                        $trip_origin->loading_point_id = $this->loading_point_id[$key];
                    }
                    if (isset($this->destination_id[$key])) {
                        $trip_origin->destination_id = $this->destination_id[$key];
                    }
                    if (isset($this->weight[$key])) {
                        $trip_origin->weight = $this->weight[$key];
                    }
                    if (isset($this->quantity[$key])) {
                        $trip_origin->quantity = $this->quantity[$key];
                    }
                    if (isset($this->units_of_measure_id[$key])) {
                        $trip_origin->units_of_measure_id = $this->units_of_measure_id[$key];
                    }
                    if (isset($this->litreage[$key])) {
                        $trip_origin->litreage = $this->litreage[$key];
                    }
                    if (isset($this->litreage_at_20[$key])) {
                        $trip_origin->litreage_at_20 = $this->litreage_at_20[$key];
                    }
                    if (isset($this->rate[$key])) {
                        $trip_origin->rate = $this->rate[$key];
                    }
                    if (isset($this->freight[$key])) {
                        $trip_origin->freight = $this->freight[$key];
                    }
                
                    $trip_origin->save();

                }

            }
       
            $this->dispatchBrowserEvent('hide-trip_originModal');
            $this->resetInputFields();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Loading Point(s) Added Successfully!!"
            ]);

        });

    }

    public function edit($id){

        $this->trip_destination_id = $id;
        $this->trip_destination = TripOrigin::find($id);
        $this->trip_id = $this->trip_destination->trip_id;
        $this->trip = $this->trip_destination->trip;
        $this->loading_point_id = $this->trip_destination->loading_point_id;
        $this->destination_id = $this->trip_destination->destination_id;
        $this->units_of_measure_id = $this->trip_destination->units_of_measure_id;
        $this->loading_date = $this->trip_destination->loading_date;
        $this->weight = $this->trip_destination->weight;
        $this->rate = $this->trip_destination->rate;
        $this->freight = $this->trip_destination->freight;
        $this->old_weight = $this->trip_destination->weight;
        $this->quantity = $this->trip_destination->quantity;
        $this->old_quantity = $this->trip_destination->quantity;
        $this->litreage = $this->trip_destination->litreage;
        $this->old_litreage = $this->trip_destination->litreage;
        $this->litreage_at_20 = $this->trip_destination->litreage_at_20;
        $this->old_litreage_at_20 = $this->trip_destination->litreage_at_20;
        $this->dispatchBrowserEvent('show-trip_originEditModal');

    }

    public function update(){

        DB::transaction(function () {
            if (isset($this->trip_destination_id)) {
                $trip_destination =  TripOrigin::find($this->trip_destination_id);
                $trip_destination->destination_id = $this->destination_id;
                $trip_destination->loading_point_id = $this->loading_point_id;
                $trip_destination->weight = $this->weight;
                $trip_destination->litreage = $this->litreage;
                $trip_destination->loading_date = $this->loading_date;
                $trip_destination->litreage_at_20 = $this->litreage_at_20;
                $trip_destination->quantity = $this->quantity;
                $trip_destination->units_of_measure_id = $this->units_of_measure_id;
                $trip_destination->freight = $this->freight;
                $trip_destination->rate = $this->rate;
                $trip_destination->update();

                $delivery_note = $this->trip->delivery_note;
                if (isset($delivery_note)) {
                    if ((!is_null($delivery_note->loaded_weight) && $delivery_note->loaded_weight != "") && ($this->weight != null && $this->weight != "")) {
                        $delivery_note->loaded_weight = ($delivery_note->loaded_weight - $this->old_weight ? $this->old_weight : 0) + $this->weight ? $this->weight : 0;
                    }else {
                        $delivery_note->loaded_weight = $this->weight;
                    }
                    if ((!is_null($delivery_note->loaded_quantity) && $delivery_note->loaded_quantity != "") && ($this->quantity != null && $this->quantity != "") ) {
                        $delivery_note->loaded_quantity = ($delivery_note->loaded_quantity - $this->old_quantity ? $this->old_quantity : 0) +  $this->quantity ? $this->quantity : 0;
                    }else {
                        $delivery_note->loaded_quantity = $this->quantity;
                    }
                    if ((!is_null($delivery_note->loaded_litreage) && $delivery_note->loaded_litreage != "") && ($this->litreage != null && $this->litreage != "") ) {
                        $delivery_note->loaded_litreage = ($delivery_note->loaded_litreage - $this->old_litreage ? $this->old_litreage : 0) + $this->litreage ? $this->litreage : 0;
                    }else {
                        $delivery_note->loaded_litreage = $this->litreage;
                    }
                    if ((!is_null($delivery_note->loaded_litreage_at_20) && $delivery_note->loaded_litreage_at_20 != "" ) && ($this->litreage_at_20 != null && $this->litreage_at_20 != "") ) {
                        $delivery_note->loaded_litreage = ($delivery_note->loaded_litreage - $this->old_litreage_at_20 ? $this->old_litreage_at_20 : 0) + $this->litreage_at_20 ? $this->litreage_at_20 : 0;
                    }else {
                        $delivery_note->loaded_litreage_at_20 = $this->litreage_at_20;
                    }   
                    $delivery_note->update();
                }

                $this->dispatchBrowserEvent('hide-trip_originEditModal');
                $this->resetInputFields();
                $this->dispatchBrowserEvent('alert',[
                    'type'=>'success',
                    'message'=>"Loading Points(s) Updated Successfully!!"
                ]);
            }
        });

    }

    public function refresh($category){

        if($category == "destinations"){
            $this->destinations = Destination::with('country')->where('status',1)->orderBy('city')->get()->sortBy('country.name');
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Destinations Refreshed Successfully!!."
            ]);
        }
        elseif($category == "loading_points"){
            $this->loading_points =loadingPoint::where('status',1)->orderBy('name','asc')->get();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"loading Points Refreshed Successfully!!."
            ]);
        }

    }


    public function render()
    {
        $this->trip_origins = TripOrigin::where('trip_id',$this->trip->id)->latest()->get();
        $this->destinations = Destination::orderBy('city','asc')->get();
        $this->loading_points = loadingPoint::orderBy('name','asc')->get();
        return view('livewire.trips.origins',[
            'trip_origins' => $this->trip_origins,
            'destinations' => $this->destinations,
            'loading_points' => $this->loading_points,
        ]);
    }
}
