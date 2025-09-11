<?php

namespace App\Http\Livewire\Drivers;

use App\Models\Trip;
use App\Models\Horse;
use App\Models\Trailer;
use App\Models\Vehicle;
use Livewire\Component;
use App\Models\Breakdown;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

class Breakdowns extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';
    public $search;
    protected $queryString = ['search'];
    private $breakdowns;
    public $driver;
    public $breakdown;
    public $breakdown_id;
    public $trip;
    public $trips;
    public $transporters;
    public $selectedTransporter;
    public $drivers;
    public $driver_id;
    public $trailers;
    public $selectedTrailer;
    public $assignment_horses;
    public $horses;
    public $selectedHorse;
    public $vehicles;
    public $selectedVehicle;
    public $trip_id;
    public $description;
    public $status;
    public $location;
    public $date;
    public $country_id;
    public $equipment;

    public function mount($driver){
    $this->driver = $driver;
    $this->trips = collect();
    $this->trailers = Trailer::orderBy('registration_number','asc')->get();
    $this->horses = Horse::orderBy('registration_number','asc')->get();
    $this->vehicles = Vehicle::orderBy('registration_number','asc')->get();

    }

    public function updatedSelectedHorse($id){
        if(!is_null($id)){
             $this->trips = Trip::select('id', 'trip_number', 'trip_ref', 'customer_id', 'from', 'to', 'loading_point_id', 'offloading_point_id')
                    ->with([
                        'customer:id,name',
                        'loading_point:id,name',
                        'offloading_point:id,name'
                    ])
                    ->whereYear('start_date',date('Y'))
                    ->where('driver_id',$this->driver->id)
                    ->where('horse_id',$id)
                    ->orderBy('start_date', 'desc')
                    ->get();
        }
       
    }
    
    public function updatedSelectedVehicle($id){
        if(!is_null($id)){
             $this->trips = Trip::select('id', 'trip_number', 'trip_ref', 'customer_id', 'from', 'to', 'loading_point_id', 'offloading_point_id')
                    ->with([
                        'customer:id,name',
                        'loading_point:id,name',
                        'offloading_point:id,name'
                    ])
                    ->whereYear('start_date',date('Y'))
                    ->where('driver_id',$this->driver->id)
                    ->where('vehicle_id',$id)
                    ->orderBy('start_date', 'desc')
                    ->get();
        }
       
    }
    
    public function updatedSelectedTrailer($id){
        if(!is_null($id)){
             $this->trips = Trip::select('id', 'trip_number', 'trip_ref', 'customer_id', 'from', 'to', 'loading_point_id', 'offloading_point_id')
                    ->with([
                        'customer:id,name',
                        'loading_point:id,name',
                        'offloading_point:id,name'
                    ])
                    ->whereYear('start_date',date('Y'))
                    ->where('driver_id',$this->driver->id)
                    ->whereHas('trailers', function($q){
                        $q->where('trailer_id', $id);
                    })
                    ->orderBy('start_date', 'desc')
                    ->get();
        }
       
    }


    private function resetInputFields(){
        $this->trip_id = "" ;
        $this->selectedHorse = "" ;
        $this->driver_id = "" ;
        $this->selectedTrailer = "" ;
        $this->selectedVehicle = "" ;
        $this->status = "";
        $this->description = "";
        $this->location = "";
        $this->date = "";

    }

        public function breakdownNumber(){

        if (isset(Auth::user()->company)) {
            $str = Auth::user()->company->name;
            $words = explode(' ', $str);
            if (isset($words[1][0])) {
                $initials = $words[0][0].$words[1][0];
            }else {
                $initials = $words[0][0];
            }
        }elseif (isset(Auth::user()->employee->company)) {
            $str = Auth::user()->employee->company->name;
            $words = explode(' ', $str);
            if (isset($words[1][0])) {
                $initials = $words[0][0].$words[1][0];
            }else {
                $initials = $words[0][0];
            }
        }

        $breakdown = Breakdown::orderBy('id','desc')->first();

        if (!$breakdown) {
            $breakdown_number =  $initials .'TI'. str_pad(1, 5, "0", STR_PAD_LEFT);
        }else {
            $number = $breakdown->id + 1;
            $breakdown_number =  $initials .'TI'. str_pad($number, 5, "0", STR_PAD_LEFT);
        }

        return  $breakdown_number;
    }

    public function store(){

        $breakdown = new Breakdown;
        $breakdown->user_id = Auth::user()->id;
        $breakdown->trip_id = $this->trip_id;
        $breakdown->breakdown_number = $this->breakdownNumber();
        $breakdown->horse_id = $this->selectedHorse ?? null;
        $breakdown->driver_id = $this->driver->id ?? null;
        $breakdown->trailer_id = $this->selectedTrailer ?? null;
        $breakdown->vehicle_id = $this->selectedVehicle ?? null;
        $breakdown->date = $this->date;
        $breakdown->location = $this->location;
        $breakdown->description = $this->description;
        $breakdown->save();
        

        $this->dispatchBrowserEvent('hide-breakdownModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Incident Recorded Successfully!!"
        ]);

    }

    public function edit($id){
        $this->breakdown_id = $id;
        $breakdown = Breakdown::find($id);
        $this->trip_id = $breakdown->trip_id;
        $this->selectedHorse = $breakdown->horse_id;
        $this->selectedVehicle = $breakdown->vehicle_id;
        $this->selectedTrailer = $breakdown->trailer_id;
        $this->date = $breakdown->date;
        $this->description = $breakdown->description;
        $this->location = $breakdown->location;
        
    }

      public function update(){

        $breakdown = Breakdown::find($this->breakdown_id);
        $breakdown->trip_id = $this->trip_id;
        $breakdown->horse_id = $this->selectedHorse ?? null;
        $breakdown->driver_id = $this->driver->id ?? null;
        $breakdown->trailer_id = $this->selectedTrailer ?? null;
        $breakdown->vehicle_id = $this->selectedVehicle ?? null;
        $breakdown->date = $this->date;
        $breakdown->location = $this->location;
        $breakdown->description = $this->description;
        $breakdown->update();
        

        $this->dispatchBrowserEvent('hide-breakdownEditModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Incident Updated Successfully!!"
        ]);

    }

    public function render()
    {
        return view('livewire.drivers.breakdowns',[
            'breakdowns' => Breakdown::whereYear('date',date('Y'))
            ->where('driver_id',$this->driver->id)->paginate(10)
        ]);
    }
}
