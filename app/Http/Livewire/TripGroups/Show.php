<?php

namespace App\Http\Livewire\TripGroups;

use App\Models\Trip;
use Livewire\Component;
use App\Models\TripGroup;
use App\Exports\TripsExport;
use Livewire\WithPagination;
use Maatwebsite\Excel\Excel;
use App\Exports\TripsTrackingExport;

class Show extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';
    
    public $current_trips;
    public $selectedTrip;
    private $trips;
    public $trip_id;
    public $trip_groups; 
    public $trip_group_id;
    public $search_id;
    public $from;
    public $to;

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

    public function exportTripsTrackingExcel(Excel $excel){
        return $excel->download(new TripsTrackingExport($this->trip_group_id), 'trips_tracking_' .time().'.xlsx');
    }

    public function mount($id){
        $this->trip_group_id = $id;
        $this->trip_group = TripGroup::find($id);
        $this->search_id = $id;
        $this->category = 'TripGroup';
        $this->current_trips = Trip::where('trip_status','!=','Offloaded')->where('trip_group_id', null)->where('authorization', 'approved')->orderBy('trip_number','desc')->get();
       
    }
   
    public function removeTrip($id){
        $trip = Trip::find($id);
        $trip->trip_group_id = Null;
        $trip->update();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Trip removed from Tracking Group Successfully!!"
        ]);
        return redirect(request()->header('Referer'));
    }

    public function addTrips(){

       

        if (isset($this->selectedTrip)) {
           
            foreach ($this->selectedTrip as $key => $value) {
                
                    $trip = Trip::find($this->selectedTrip[$key]);
                    $trip->trip_group_id = $this->trip_group_id;
                    $trip->update();
            }
        }

        $this->dispatchBrowserEvent('hide-addTripsModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Trip(s) added to a Tracking Group Successfully!!"
        ]);
        return redirect(request()->header('Referer'));
    }
    
     

    public function updated($value){
        $this->validateOnly($value);
    }
    protected $rules = [
        'selectedTrip.0' => 'required',
        'selectedTrip.*' => 'required',
    ];

    private function resetInputFields(){
        $this->selectedTrip = null;
    }
    
    public function render()
    {
        if (isset($this->searchTrip)) {
            $this->current_trips = Trip::query()->with('customer','loading_point','offloading_point','horse', 'vehicle')
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

       

        return view('livewire.trip-groups.show',[
            'trips' => Trip::where('trip_group_id', $this->trip_group_id)->paginate(10),
        ]);
    }
}
