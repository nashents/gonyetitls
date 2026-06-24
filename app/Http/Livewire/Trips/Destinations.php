<?php

namespace App\Http\Livewire\Trips;

use App\Models\Destination;
use App\Models\OffloadingPoint;
use App\Models\Trip;
use App\Models\TripDestination;
use App\Models\UnitsOfMeasure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

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
        $this->offloading_point_id = [];
        $this->units_of_measure_id = [];
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
        'offloading_date' => 'required',
    ];

    public function mount($trip){
        $this->trip = $trip;
        $this->trip_id = $trip->id;
        $this->cargo = $this->trip->cargo;
        $this->trip_destinations = $this->trip->trip_destinations;
        $this->destinations = Destination::orderBy('city','asc')->get();
        $this->units_of_measures = UnitsOfMeasure::orderBy('name','asc')->get();
        $this->offloading_points = OffloadingPoint::orderBy('name','asc')->get();
    }

    public function store(){

        $this->validate();

       
        DB::transaction(function () {

        if (!empty($this->destination_id) && is_array($this->destination_id)) {

            foreach ($this->destination_id as $key => $destinationId) {

                $offloadingPointId = $this->offloading_point_id[$key] ?? null;

                // Skip empty rows
                if (blank($destinationId) && blank($offloadingPointId)) {
                    continue;
                }

                TripDestination::updateOrCreate(
                    [
                        'trip_id'             => $this->trip_id ?: null,
                        'destination_id'      => $destinationId ?: null,
                        'offloading_point_id' => $offloadingPointId ?: null,
                    ],
                    [
                        'user_id'             => Auth::id(),
                        'offloading_date'     => $this->offloading_date[$key] ?? null,
                        'weight'              => $this->weight[$key] ?? null,
                        'quantity'            => $this->quantity[$key] ?? null,
                        'units_of_measure_id' => $this->units_of_measure_id[$key] ?? null,
                        'litreage'            => $this->litreage[$key] ?? null,
                        'litreage_at_20'      => $this->litreage_at_20[$key] ?? null,
                        'rate'                => $this->rate[$key] ?? null,
                        'freight'             => $this->freight[$key] ?? null,
                    ]
                );
            }
        }
       
        $this->dispatchBrowserEvent('hide-trip_destinationModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Destination(s) Added Successfully!!"
        ]);

        });

  
    }

  
    public function edit($id)
    {
        $tripDestination = TripDestination::with('trip.delivery_note')->findOrFail($id);

        $this->trip_destination_id = $tripDestination->id;
        $this->trip_destination    = $tripDestination;

        $this->trip_id = $tripDestination->trip_id;
        $this->trip    = $tripDestination->trip;

        $this->destination_id        = $tripDestination->destination_id;
        $this->offloading_point_id   = $tripDestination->offloading_point_id;
        $this->units_of_measure_id   = $tripDestination->units_of_measure_id;
        $this->offloading_date       = $tripDestination->offloading_date;

        $this->weight         = $tripDestination->weight;
        $this->old_weight     = $tripDestination->weight;

        $this->quantity       = $tripDestination->quantity;
        $this->old_quantity   = $tripDestination->quantity;

        $this->litreage       = $tripDestination->litreage;
        $this->old_litreage   = $tripDestination->litreage;

        $this->litreage_at_20     = $tripDestination->litreage_at_20;
        $this->old_litreage_at_20 = $tripDestination->litreage_at_20;

        $this->rate    = $tripDestination->rate;
        $this->freight = $tripDestination->freight;

        $this->dispatchBrowserEvent('show-trip_destinationEditModal');
    }

    public function update()
    {
        $this->validate([
            'trip_destination_id'   => 'required|exists:trip_destinations,id',
            'destination_id'        => 'nullable|exists:destinations,id',
            'offloading_point_id'   => 'nullable|exists:offloading_points,id',
            'units_of_measure_id'   => 'nullable|exists:units_of_measures,id',
            'offloading_date'       => 'nullable|date',
            'weight'                => 'nullable|numeric|min:0',
            'quantity'              => 'nullable|numeric|min:0',
            'litreage'              => 'nullable|numeric|min:0',
            'litreage_at_20'        => 'nullable|numeric|min:0',
            'rate'                  => 'nullable|numeric|min:0',
            'freight'               => 'nullable|numeric|min:0',
        ]);

        DB::transaction(function () {

            $tripDestination = TripDestination::with('trip.delivery_note')
                ->findOrFail($this->trip_destination_id);

            $tripDestination->update([
                'destination_id'        => $this->destination_id ?: null,
                'offloading_point_id'   => $this->offloading_point_id ?: null,
                'units_of_measure_id'   => $this->units_of_measure_id ?: null,
                'offloading_date'       => $this->offloading_date ?: null,
                'weight'                => $this->weight ?: null,
                'quantity'              => $this->quantity ?: null,
                'litreage'              => $this->litreage ?: null,
                'litreage_at_20'        => $this->litreage_at_20 ?: null,
                'rate'                  => $this->rate ?: null,
                'freight'               => $this->freight ?: null,
            ]);

            $deliveryNote = optional($tripDestination->trip)->delivery_note;

            if ($deliveryNote) {

                
                $deliveryNote->update([
                    'offloaded_weight' => $this->adjustDeliveryNoteValue(
                        $deliveryNote->offloaded_weight,
                        $this->old_weight,
                        $this->weight
                    ),

                    'offloaded_quantity' => $this->adjustDeliveryNoteValue(
                        $deliveryNote->offloaded_quantity,
                        $this->old_quantity,
                        $this->quantity
                    ),

                    'offloaded_litreage' => $this->adjustDeliveryNoteValue(
                        $deliveryNote->offloaded_litreage,
                        $this->old_litreage,
                        $this->litreage
                    ),

                    'offloaded_litreage_at_20' => $this->adjustDeliveryNoteValue(
                        $deliveryNote->offloaded_litreage_at_20,
                        $this->old_litreage_at_20,
                        $this->litreage_at_20
                    ),
                ]);
            }
        });

        $this->dispatchBrowserEvent('hide-trip_destinationEditModal');

        $this->resetInputFields();

        $this->dispatchBrowserEvent('alert', [
            'type'    => 'success',
            'message' => 'Destination updated successfully.',
        ]);
    }

    private function adjustDeliveryNoteValue($currentValue, $oldValue, $newValue)
    {
        $currentValue = is_numeric($currentValue) ? (float) $currentValue : 0;
        $oldValue     = is_numeric($oldValue) ? (float) $oldValue : 0;
        $newValue     = is_numeric($newValue) ? (float) $newValue : 0;

        return ($currentValue - $oldValue) + $newValue;
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

    public function delete($id){
        $this->trip_destination_id = $id;
        $this->dispatchBrowserEvent('show-deleteModal');
    }

    public function destroy(){
        
        $destination = TripDestination::find($this->trip_destination_id);
        $destination->delete();

        $this->dispatchBrowserEvent('hide-deleteModal');
        $this->dispatchBrowserEvent('alert', [
            'type'    => 'success',
            'message' => 'Offloading Point Deleted Successfully!!',
        ]);

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
