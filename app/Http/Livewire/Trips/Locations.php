<?php

namespace App\Http\Livewire\Trips;

use App\Models\Trip;
use App\Models\Country;
use Livewire\Component;
use App\Models\LocationPin;
use App\Models\TripLocation;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;
use Stevebauman\Location\Facades\Location;

class Locations extends Component
{
    
    use WithFileUploads;

    public $trip;
    public $trip_id;
    public $countries;
    public $country_id;
    public $user_id;
    public $trip_locations;
    public $trip_location_id;

    public $city;
    public $suburb;
    public $street_address;
    public $description;
    public $pin;


    private function resetInputFields(){
        $this->city = "" ;
        $this->country_id = "";
        $this->suburb = "";
        $this->street_address = "";
        $this->description = "";

    }

    public function updated($value){
        $this->validateOnly($value);
    }
    protected $rules = [
        'country_id' => 'required',
    ];

    public function mount($trip){
    $this->trip = $trip;
    $this->trip_id = $trip->id;
    $this->countries = Country::latest()->get();
    $this->trip_locations =  $this->trip->trip_locations;
    }

    public function store(){
        try{
        $trip_location = new TripLocation;
        $trip_location->user_id = Auth::user()->id;
        $trip_location->trip_id = $this->trip->id;
        // $trip_location->driver_id = $this->trip->driver_id;
        $trip_location->country_id = $this->country_id;
        $trip_location->city = $this->city;
        $trip_location->suburb = $this->suburb;
        $trip_location->street_address = $this->street_address;
        $trip_location->description = $this->description;
        $trip_location->save();

        $ip_address = request()->ip; 
            if ($position = Location::get($ip_address)) {
                $location_pin = new LocationPin;
                $location_pin->trip_location_id = $trip_location->id;
                $location_pin->country = $position->countryName;
                $location_pin->country_code = $position->countryCode;
                $location_pin->region = $position->regionName;
                $location_pin->region_code = $position->regionCode;
                $location_pin->city = $position->cityName;
                $location_pin->zipcode = $position->zipCode;
                $location_pin->latitude = $position->latitude;
                $location_pin->longitude = $position->longitude;
                $location_pin->save();
            } 

        $this->dispatchBrowserEvent('hide-locationModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Location Updated Successfully!!"
        ]);
                    

        }catch(\Exception $e){
            // Set Flash Message
            $this->dispatchBrowserEvent('alert',[
                'type'=>'error',
                'message'=>"Something went wrong while uploading location(s)!!"
            ]);
        }
    }
    public function edit($id){

        $trip_location = TripLocation::find($id);
        $this->user_id = $trip_location->user_id;
        $this->trip_id = $trip_location->trip_id;
        $this->country_id = $trip_location->country_id;
        $this->city = $trip_location->city;
        $this->suburb = $trip_location->suburb;
        $this->street_address = $trip_location->street_address;
        $this->description = $trip_location->description;
        $this->trip_location_id = $trip_location->id;
        $this->dispatchBrowserEvent('show-locationEditModal');

        }

        public function update()
        {
            if ($this->trip_location_id) {
                try{
                $trip_location = TripLocation::find($this->trip_location_id);
                $trip_location->trip_id = $this->trip_id;
                $trip_location->user_id = Auth::user()->id;
                $trip_location->country_id = $this->country_id;
                $trip_location->city = $this->city;
                $trip_location->suburb = $this->suburb;
                $trip_location->street_address = $this->street_address;
                $trip_location->description = $this->description; 
                $trip_location->update();

                $this->dispatchBrowserEvent('hide-locationEditModal');
                $this->resetInputFields();
                $this->dispatchBrowserEvent('alert',[
                    'type'=>'success',
                    'message'=>"Location Updated Successfully!!"
                ]);
            }catch(\Exception $e){
                // Set Flash Message
                $this->dispatchBrowserEvent('alert',[
                    'type'=>'error',
                    'message'=>"Something went wrong while updating location(s)!!"
                ]);
            }

            }
        }
    public function render()
    {
        $this->trip_locations = TripLocation::where('trip_id', $this->trip->id)->latest()->get();
        return view('livewire.trips.locations',[
            'trip_locations'=> $this->trip_locations
        ]);
    }
}
