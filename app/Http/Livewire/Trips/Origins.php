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
    public $trip_origin_id;
    public $trip_origin;


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

    public function store()
    {
        $this->validate();

        DB::transaction(function () {

            $rows = [];

            if (!empty($this->destination_id) && is_array($this->destination_id)) {

                foreach ($this->destination_id as $key => $destinationId) {

                    // Skip empty rows
                    if (blank($destinationId) && blank($this->loading_point_id[$key] ?? null)) {
                        continue;
                    }

                    $rows[] = [
                        'user_id'             => Auth::id(),
                        'trip_id'             => $this->trip_id ?: null,
                        'loading_date'        => $this->loading_date[$key] ?? null,
                        'loading_point_id'    => $this->loading_point_id[$key] ?? null,
                        'destination_id'      => $destinationId ?: null,
                        'weight'              => $this->weight[$key] ?? null,
                        'quantity'            => $this->quantity[$key] ?? null,
                        'units_of_measure_id' => $this->units_of_measure_id[$key] ?? null,
                        'litreage'            => $this->litreage[$key] ?? null,
                        'litreage_at_20'      => $this->litreage_at_20[$key] ?? null,
                        'rate'                => $this->rate[$key] ?? null,
                        'freight'             => $this->freight[$key] ?? null,
                        'created_at'          => now(),
                        'updated_at'          => now(),
                    ];
                }
            }

            if (!empty($rows)) {
                TripOrigin::insert($rows);
            }

            $this->dispatchBrowserEvent('hide-trip_originModal');

            $this->resetInputFields();

            $this->dispatchBrowserEvent('alert', [
                'type'    => 'success',
                'message' => 'Loading point(s) added successfully.',
            ]);
        });
    }
    public function edit($id)
    {
        $tripOrigin = TripOrigin::with('trip.delivery_note')->findOrFail($id);

        $this->trip_origin_id = $tripOrigin->id;
        $this->trip_origin    = $tripOrigin;

        $this->trip_id = $tripOrigin->trip_id;
        $this->trip    = $tripOrigin->trip;

        $this->loading_point_id    = $tripOrigin->loading_point_id;
        $this->destination_id      = $tripOrigin->destination_id;
        $this->units_of_measure_id = $tripOrigin->units_of_measure_id;
        $this->loading_date        = $tripOrigin->loading_date;

        $this->weight     = $tripOrigin->weight;
        $this->quantity   = $tripOrigin->quantity;
        $this->litreage   = $tripOrigin->litreage;
        $this->litreage_at_20 = $tripOrigin->litreage_at_20;

        $this->rate    = $tripOrigin->rate;
        $this->freight = $tripOrigin->freight;

        $this->dispatchBrowserEvent('show-trip_originEditModal');
    }

    public function update()
    {
        $this->validate([
            'trip_origin_id'        => 'required|exists:trip_origins,id',
            'destination_id'        => 'nullable|exists:destinations,id',
            'loading_point_id'      => 'nullable|exists:loading_points,id',
            'units_of_measure_id'   => 'nullable|exists:units_of_measures,id',
            'loading_date'          => 'nullable|date',
            'weight'                => 'nullable|numeric|min:0',
            'quantity'              => 'nullable|numeric|min:0',
            'litreage'              => 'nullable|numeric|min:0',
            'litreage_at_20'        => 'nullable|numeric|min:0',
            'rate'                  => 'nullable|numeric|min:0',
            'freight'               => 'nullable|numeric|min:0',
        ]);

        DB::transaction(function () {

            $tripOrigin = TripOrigin::with('trip.delivery_note')
                ->findOrFail($this->trip_origin_id);

            $tripOrigin->update([
                'destination_id'        => $this->destination_id ?: null,
                'loading_point_id'      => $this->loading_point_id ?: null,
                'units_of_measure_id'   => $this->units_of_measure_id ?: null,
                'loading_date'          => $this->loading_date ?: null,
                'weight'                => $this->weight ?: null,
                'quantity'              => $this->quantity ?: null,
                'litreage'              => $this->litreage ?: null,
                'litreage_at_20'        => $this->litreage_at_20 ?: null,
                'rate'                  => $this->rate ?: null,
                'freight'               => $this->freight ?: null,
            ]);

            $deliveryNote = optional($tripOrigin->trip)->delivery_note;

            if ($deliveryNote) {
                $this->recalculateLoadedDeliveryNoteTotals($tripOrigin->trip, $deliveryNote);
            }

            $this->dispatchBrowserEvent('hide-trip_originEditModal');

            $this->resetInputFields();

            $this->dispatchBrowserEvent('alert', [
                'type'    => 'success',
                'message' => 'Loading point updated successfully.',
            ]);
        });
    }

    private function recalculateLoadedDeliveryNoteTotals($trip, $deliveryNote)
    {
        if (!$trip || !$deliveryNote) {
            return;
        }

        $deliveryNote->update([
            'loaded_weight'           => $trip->trip_origins()->sum('weight'),
            'loaded_quantity'         => $trip->trip_origins()->sum('quantity'),
            'loaded_litreage'         => $trip->trip_origins()->sum('litreage'),
            'loaded_litreage_at_20'   => $trip->trip_origins()->sum('litreage_at_20'),
        ]);
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
