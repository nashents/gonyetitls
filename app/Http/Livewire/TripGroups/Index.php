<?php

namespace App\Http\Livewire\TripGroups;

use App\Models\Trip;
use Livewire\Component;
use App\Models\Customer;
use App\Models\TripGroup;
use App\Models\Destination;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class Index extends Component
{

    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $destinations;
    public $destination_id;
    public $from;
    public $to;
    public $customers;
    public $customer_id;
    public $trips;
    public $trip_id;
    private $trip_groups;
    public $name;
    public $date;
    public $trip_group_id;
    public $user_id;
    public $tracking_filter;
    public $status;

    public $inputs = [];
    public $i = 1;
    public $n = 1;

    public $searchTrip;
    protected $queryString = ['searchTrip'];

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


    public function mount(){
        $this->customers = Customer::orderBy('name','asc')->get();
        $this->destinations = Destination::with('country')->get()->sortBy('city')->sortBy('country.name');
        $this->tracking_filter = "all";
        $this->trips = Trip::where('trip_group_id', null)->where('trip_status','!=','Offloaded')->where('authorization', 'approved')->orderBy('trip_number','desc')->get();
    }

    public function updated($value){
        $this->validateOnly($value);
    }
    protected $rules = [
        'name' => 'required|unique:trip_groups,name,NULL,id,deleted_at,NULL|string|min:2',
        'trip_id.0' => 'required',
        'trip_id.*' => 'required',
        'date' => 'required',
        'customer_id' => 'required',
        'from' => 'required',
        'to' => 'required',
    ];


       public function refresh($category){

        if($category == "destinations"){
            $this->destinations = Destination::with('country')->get()->sortBy('city')->sortBy('country.name');
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Destinations Refreshed Successfully!!."
            ]);
        }
       
    }
    private function resetInputFields(){
        $this->name = '';
        $this->customer_id = '';
        $this->date = '';
        $this->from = '';
        $this->to = '';
        $this->trip_id = null;
    }

    public function store(){
        // try{

            $trip_group = new TripGroup;
            $trip_group->user_id = Auth::user()->id;
            $trip_group->customer_id = $this->customer_id;
            $trip_group->date = $this->date;
            $trip_group->from = $this->from;
            $trip_group->to = $this->to;
            $trip_group->name = $this->name;
            $trip_group->status = 1;
            $trip_group->save();

           

        $this->dispatchBrowserEvent('hide-trip_groupModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Trip Tracking Group Created Successfully!!"
        ]);

        // return redirect()->route('trip_groups.index');

    //     }
    //     catch(\Exception $e){
    //     // Set Flash Message
    //     $this->dispatchBrowserEvent('alert',[
    //         'type'=>'error',
    //         'message'=>"Something goes wrong while creating tracking group!!"
    //     ]);
    // }
    }

    public function edit($id){
        
    $trip_group = TripGroup::find($id);
    $this->user_id = $trip_group->user_id;
    $this->name = $trip_group->name;
    $this->date = $trip_group->date;
    $this->customer_id = $trip_group->customer_id;
    $this->from = $trip_group->from;
    $this->to = $trip_group->to;
    $this->status = $trip_group->status;
    $this->trip_group_id = $trip_group->id;
    $this->dispatchBrowserEvent('show-trip_groupEditModal');

    }


    public function update()
    {
        if ($this->trip_group_id) {
            try{

                $customer_name = str_replace(' ', '', strtolower(Customer::find($this->customer_id)->name));
                $from_destination = Destination::find($this->from);
                $from_country = Destination::find($this->from)->country;
                if (isset($from_country)) {
                $origin = $from_country->name.'-'.$from_destination->city; 
                }else{
                    $origin = "";
                }
        
                $to_destination = Destination::find($this->to);
                $to_country = Destination::find($this->to)->country;
                if (isset($to_country)) {
                 $destination =   str_replace(' ', '', strtolower($to_country->name.'-'.$to_destination->city));
                }else {
                    $destination = "";
                }
        

            $trip_group = TripGroup::find($this->trip_group_id);
            $trip_group->user_id = Auth::user()->id;
            $trip_group->customer_id = $this->customer_id;
            $trip_group->date = $this->date;
            $trip_group->from = $this->from;
            $trip_group->to = $this->to;
            $trip_group->name = $customer_name.'-'.$this->date.'-'.$origin.'-'.$destination;
            $trip_group->status = $this->status;
            $trip_group->update();

            $this->dispatchBrowserEvent('hide-trip_groupEditModal');
            $this->resetInputFields();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Trip Tracking Group Updated Successfully!!"
            ]);


            // return redirect()->route('trip_groups.index');
            }
            catch(\Exception $e){
            $this->dispatchBrowserEvent('hide-trip_groupEditModal');
            $this->dispatchBrowserEvent('alert',[
                'type'=>'error',
                'message'=>"Something goes wrong while creating trip tacking group!!"
            ]);
          }
        }
    }

    public function makeName(){
        if (isset($this->customer_id) && isset($this->from) && isset($this->to) && isset($this->date)) {
            if (isset($this->customer_id)) {
                $customer_name = str_replace(' ', '', strtolower(Customer::find($this->customer_id)->name));
            }
           

            $from_destination = Destination::find($this->from);
            $from_country = Destination::find($this->from)->country;
    
            if (isset($from_country)) {
            $origin = $from_country->name.'-'.$from_destination->city; 
            }else{
                $origin = "";
            }
    
            $to_destination = Destination::find($this->to);
            $to_country = Destination::find($this->to)->country;
            if (isset($to_country)) {
             $destination =   str_replace(' ', '', strtolower($to_country->name.'-'.$to_destination->city));
            }else {
                $destination = "";
            }
    
    
            $this->name = $customer_name.'-'.$this->date.'-'.$origin.'-'.$destination;
        }
    }

    public function close($id){
        $trip_group = TripGroup::find($id);
        $trip_group->status = 0;
        $trip_group->update();

        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Trip Tracking Group marked as completed successfully!!"
        ]);

    }


    public function render()
    {

        if (isset($this->searchTrip)) {

            $this->trips = Trip::query()->with('customer','loading_point','offloading_point','horse', 'vehicle')
            ->where('trip_group_id', null)
            ->where('trip_status','!=','Offloaded')
            ->where('authorization', 'approved')
            ->where('trip_number', 'like', '%'.$this->searchTrip.'%')
            ->orWhereHas('customer', function ($query) {
            return $query->where('name', 'like', '%'.$this->searchTrip.'%');
            })
            ->orWhereHas('horse', function ($query) {
            return $query->where('registration_number', 'like', '%'.$this->searchTrip.'%');
            })
            ->orWhereHas('loading_point', function ($query) {
            return $query->where('name', 'like', '%'.$this->searchTrip.'%');
            })
            ->orWhereHas('offloading_point', function ($query) {
            return $query->where('name', 'like', '%'.$this->searchTrip.'%');
            })->get();

        }

        $query = TripGroup::query();
        if ($this->tracking_filter != "all") {
            $query->where('status', $this->tracking_filter);
        }

        $tracking_groups = $query->orderBy('created_at','desc')->paginate(10);
        
        return view('livewire.trip-groups.index',[
            'trip_groups'=>  $tracking_groups,
            
        ]);
    }
}
