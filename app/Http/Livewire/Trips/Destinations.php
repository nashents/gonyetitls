<?php

namespace App\Http\Livewire\Trips;

use App\Models\Trip;
use Livewire\Component;
use App\Models\Destination;
use App\Models\Measurement;
use App\Models\OffloadingPoint;
use App\Models\TripDestination;
use Illuminate\Support\Facades\Auth;

class Destinations extends Component
{
    public $trip;
    public $trip_id;
    public $cargo;
    public $trip_destinations;
    public $destinations;
    public $destination_id;
    public $offloading_points;
    public $offloading_point_id;
    public $weight;
    public $rate;
    public $freight;
    public $old_weight;
    public $quantity;
    public $old_quantity;
    public $litreage;
    public $old_litreage;
    public $offloading_date;
    public $litreage_at_20;
    public $old_litreage_at_20;
    public $measurements;
    public $measurement_id;


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
        $this->offloading_point_id = [];
        $this->measurement_id = [];
        $this->weight = [];
        $this->quantity = [];
        $this->litreage = [];
        $this->litreage_at_20 = [];
        $this->offloading_date = [];

    }

    public function updated($value){
        $this->validateOnly($value);
    }

    
    protected $rules = [
        'destination_id' => 'required',
        'offloading_point_id' => 'required',
        // 'measurement_id' => 'required',
        'offloading_date' => 'required',
    ];

    public function mount($trip){
        $this->trip = $trip;
        $this->trip_id = $trip->id;
        $this->cargo = $this->trip->cargo;
        $this->trip_destinations = $this->trip->trip_destinations;
        $this->destinations = Destination::orderBy('city','asc')->get();
        $this->measurements = Measurement::orderBy('name','asc')->get();
        $this->offloading_points = OffloadingPoint::orderBy('name','asc')->get();
    }

    public function store(){

        $this->validate();

        // try{
        if (isset($this->destination_id)) {
            foreach ($this->destination_id as $key => $value) {
                $trip_destination = new TripDestination;
                $trip_destination->user_id = Auth::user()->id;
                $trip_destination->trip_id = $this->trip_id;
                if (isset($this->offloading_date[$key])) {
                    $trip_destination->offloading_date = $this->offloading_date[$key];
                }
                if (isset($this->offloading_point_id[$key])) {
                    $trip_destination->offloading_point_id = $this->offloading_point_id[$key];
                }
                if (isset($this->destination_id[$key])) {
                    $trip_destination->destination_id = $this->destination_id[$key];
                }
                if (isset($this->weight[$key])) {
                    $trip_destination->weight = $this->weight[$key];
                }
                if (isset($this->quantity[$key])) {
                    $trip_destination->quantity = $this->quantity[$key];
                }
                if (isset($this->measurement_id[$key])) {
                    $trip_destination->measurement_id = $this->measurement_id[$key];
                }
                if (isset($this->litreage[$key])) {
                    $trip_destination->litreage = $this->litreage[$key];
                }
                if (isset($this->litreage_at_20[$key])) {
                    $trip_destination->litreage_at_20 = $this->litreage_at_20[$key];
                }
                if (isset($this->rate[$key])) {
                    $trip_destination->rate = $this->rate[$key];
                }
                if (isset($this->freight[$key])) {
                    $trip_destination->freight = $this->freight[$key];
                }
               
                $trip_destination->save();


                // $delivery_note = $this->trip->delivery_note;
                // if (isset($delivery_note)) {
                //     if (isset($this->weight[$key]) && !is_null($delivery_note->offloaded_weight)) {
                //         $delivery_note->offloaded_weight = $delivery_note->offloaded_weight + $this->weight[$key];
                //     }else {
                //         if (isset($this->weight[$key])) {
                //             $delivery_note->offloaded_weight = $this->weight[$key];
                //         }
                //     }
                //     if (isset($this->quantity[$key]) && !is_null($delivery_note->offloaded_quantity)) {
                //         $delivery_note->offloaded_quantity = $delivery_note->offloaded_quantity +  $this->quantity[$key];
                //     }else {
                //         if(isset($this->quantity[$key])){
                //             $delivery_note->offloaded_quantity = $this->quantity[$key];
                //         }
                       
                //     }
                //     if (isset($this->litreage[$key]) && !is_null($delivery_note->offloaded_litreage)) {
                //         $delivery_note->offloaded_litreage = $delivery_note->offloaded_litreage + $this->litreage[$key];
                //     }else {
                //         if (isset($this->litreage[$key])) {
                //             $delivery_note->offloaded_litreage =  $this->litreage[$key];
                //         }
                       
                //     }
                //     if (isset($this->litreage_at_20[$key]) && !is_null($delivery_note->offloaded_litreage_at_20)) {
                //         $delivery_note->offloaded_litreage_at_20 = $delivery_note->offloaded_litreage_at_20 + $this->litreage_at_20[$key];
                //     }else {
                //         if (isset($this->litreage_at_20[$key])) {
                //             $delivery_note->offloaded_litreage_at_20 = $this->litreage_at_20[$key];
                //         }
                       
                //     }
                //     // $delivery_note->updated_from_offloading_points = 1;
                //     $delivery_note->update();
                // }

            }
        }
       
        $this->dispatchBrowserEvent('hide-trip_destinationModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Destination(s) Added Successfully!!"
        ]);

    // }catch(\Exception $e){
    //     // Set Flash Message
    //     $this->dispatchBrowserEvent('alert',[
    //         'type'=>'error',
    //         'message'=>"Something went wrong while adding destinations(s)!!"
    //     ]);
    // }
    }

    public function edit($id){
        $this->trip_destination_id = $id;
        $this->trip_destination = TripDestination::find($id);
        $this->trip_id = $this->trip_destination->trip_id;
        $this->trip = $this->trip_destination->trip;
        $this->offloading_point_id = $this->trip_destination->offloading_point_id;
        $this->destination_id = $this->trip_destination->destination_id;
        $this->measurement_id = $this->trip_destination->measurement_id;
        $this->offloading_date = $this->trip_destination->offloading_date;
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
        $this->dispatchBrowserEvent('show-trip_destinationEditModal');
    }

    public function update(){

        // try{
        if (isset($this->trip_destination_id)) {
                $trip_destination =  TripDestination::find($this->trip_destination_id);
                $trip_destination->destination_id = $this->destination_id;
                $trip_destination->offloading_point_id = $this->offloading_point_id;
                $trip_destination->weight = $this->weight;
                $trip_destination->litreage = $this->litreage;
                $trip_destination->offloading_date = $this->offloading_date;
                $trip_destination->litreage_at_20 = $this->litreage_at_20;
                $trip_destination->quantity = $this->quantity;
                $trip_destination->measurement_id = $this->measurement_id;
                $trip_destination->freight = $this->freight;
                $trip_destination->rate = $this->rate;
                $trip_destination->update();

                $delivery_note = $this->trip->delivery_note;
                if (isset($delivery_note)) {
                    if ((!is_null($delivery_note->offloaded_weight) && $delivery_note->offloaded_weight != "") && ($this->weight != null && $this->weight != "")) {
                        $delivery_note->offloaded_weight = ($delivery_note->offloaded_weight - $this->old_weight ? $this->old_weight : 0) + $this->weight ? $this->weight : 0;
                    }else {
                        $delivery_note->offloaded_weight = $this->weight;
                    }
                    if ((!is_null($delivery_note->offloaded_quantity) && $delivery_note->offloaded_quantity != "") && ($this->quantity != null && $this->quantity != "") ) {
                        $delivery_note->offloaded_quantity = ($delivery_note->offloaded_quantity - $this->old_quantity ? $this->old_quantity : 0) +  $this->quantity ? $this->quantity : 0;
                    }else {
                        $delivery_note->offloaded_quantity = $this->quantity;
                    }
                    if ((!is_null($delivery_note->offloaded_litreage) && $delivery_note->offloaded_litreage != "") && ($this->litreage != null && $this->litreage != "") ) {
                        $delivery_note->offloaded_litreage = ($delivery_note->offloaded_litreage - $this->old_litreage ? $this->old_litreage : 0) + $this->litreage ? $this->litreage : 0;
                    }else {
                        $delivery_note->offloaded_litreage = $this->litreage;
                    }
                    if ((!is_null($delivery_note->offloaded_litreage_at_20) && $delivery_note->offloaded_litreage_at_20 != "" ) && ($this->litreage_at_20 != null && $this->litreage_at_20 != "") ) {
                        $delivery_note->offloaded_litreage = ($delivery_note->offloaded_litreage - $this->old_litreage_at_20 ? $this->old_litreage_at_20 : 0) + $this->litreage_at_20 ? $this->litreage_at_20 : 0;
                    }else {
                        $delivery_note->offloaded_litreage_at_20 = $this->litreage_at_20;
                    }   
                    $delivery_note->update();
                }

                $this->dispatchBrowserEvent('hide-trip_destinationEditModal');
                $this->resetInputFields();
                $this->dispatchBrowserEvent('alert',[
                    'type'=>'success',
                    'message'=>"Destination(s) Updated Successfully!!"
                ]);
        }
       
    // }catch(\Exception $e){
    //     // Set Flash Message
    //     $this->dispatchBrowserEvent('alert',[
    //         'type'=>'error',
    //         'message'=>"Something went wrong while adding destinations(s)!!"
    //     ]);
    // }
    }

    public function refresh($category){

        if($category == "destinations"){
            $this->destinations = Destination::with('country')->where('status',1)->orderBy('city')->get()->sortBy('country.name');
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Destinations Refreshed Successfully!!."
            ]);
        }
        elseif($category == "offloading_points"){
            $this->offloading_points =OffloadingPoint::where('status',1)->orderBy('name','asc')->get();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Offloading Points Refreshed Successfully!!."
            ]);
        }
    }


    public function render()
    {
        $this->trip_destinations = TripDestination::where('trip_id',$this->trip->id)->latest()->get();
        $this->destinations = Destination::orderBy('city','asc')->get();
        $this->offloading_points = OffloadingPoint::orderBy('name','asc')->get();
        return view('livewire.trips.destinations',[
            'trip_destinations' => $this->trip_destinations,
            'destinations' => $this->destinations,
            'offloading_points' => $this->offloading_points,
        ]);
    }
}
