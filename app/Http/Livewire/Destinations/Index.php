<?php

namespace App\Http\Livewire\Destinations;

use App\Models\Country;
use Livewire\Component;
use App\Models\Destination;
use Livewire\WithPagination;
use App\Http\Livewire\Concerns\HandlesMapLocation;
use Maatwebsite\Excel\Excel;
use App\Models\SageProjectLocation;
use App\Exports\DestinationsExport;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use App\Services\Sage\SageIntegration;
use Illuminate\Support\Facades\Session;
use App\Http\Livewire\Concerns\PullsFromSage;

class Index extends Component
{

    use WithPagination;
    use HandlesMapLocation;
    use PullsFromSage;

    /** Sage integration gate — controls the "Pull Sage locations" button. */
    public function getSageEnabledProperty()
    {
        return SageIntegration::enabledForUser();
    }

    /** Pull the Depature/Destination Location picklists from Sage + auto-map. */
    public function pullFromSage()
    {
        $this->dispatchSagePull('project_location', 'Sage locations');
    }

    protected $paginationTheme = 'bootstrap';

    public $search;
    protected $queryString = ['search'];
    
    private $destinations;
    public $country_id;
    public $city;
    public $long;
    public $lat;
    public $location;
    public $countries;
    public $description;
    public $status;
    public $sage_location;

    public $updateMode = false;
    public $deleteMode = false;

    public $destination_id;
    public $user_id;

    protected $listeners = ['setLocationData'];

    protected function mapNameField(): string
    {
        return 'city';
    }
 

    public function mount(){
        $this->resetPage();
        $this->countries = Country::all();
    }

    /** Reload the country dropdown after a country is added in another tab. */
    public function refreshCountries()
    {
        $this->countries = Country::all();
    }
    private function resetInputFields(){
        $this->country_id = '';
        $this->city = '';
        $this->description = '';
        $this->long = '';
        $this->lat = '';
        $this->location = '';
        $this->status = '';
        $this->sage_location = '';
    }

    public function exportDestinationsCSV(Excel $excel){

        return $excel->download(new DestinationsExport, 'destinations.csv', Excel::CSV);
    }
    public function exportDestinationsPDF(Excel $excel){

        return $excel->download(new DestinationsExport, 'destinations.pdf', Excel::DOMPDF);
    }
    public function exportDestinationsExcel(Excel $excel){

        return $excel->download(new DestinationsExport, 'destinations.xlsx');
    }
    public function updated($value){
        $this->validateOnly($value);
    }
    protected $rules = [
        'lat' => 'nullable|numeric|between:-90,90',
        'long' => 'nullable|numeric|between:-180,180',
        'country_id' => 'required',
        'city' => 'required|unique:destinations,city,NULL,id,deleted_at,NULL',
        'location' => 'required_without_all:lat,long',
        'description' => 'required',
    ];

  

    public function store(){
        try{
        $destination = new Destination;
        $destination->user_id = Auth::user()->id;
        $destination->country_id = $this->country_id;
        $destination->city = $this->city;
        $destination->long = $this->long;
        $destination->lat = $this->lat;
        $destination->location = $this->location;
        $destination->description = $this->description;
        $destination->sage_location = $this->sage_location ?: null;
        $destination->save();

        $this->dispatchBrowserEvent('hide-destinationModal');
        $this->resetInputFields();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Destination Created Successfully!!"
        ]);

    }
    catch(\Exception $e){
    // Set Flash Message
    $this->dispatchBrowserEvent('alert',[
        'type'=>'error',
        'message'=>"Something goes wrong while creating destination!!"
    ]);
     }
    }

    public function edit($id){
    $destination = Destination::find($id);
    $this->user_id = $destination->user_id;
    $this->country_id = $destination->country_id;
    $this->city = $destination->city;
    $this->location = $destination->location;
    $this->long = $destination->long;
    $this->lat = $destination->lat;
    $this->description = $destination->description;
    $this->destination_id = $destination->id;
    $this->status = $destination->status;
    $this->sage_location = $destination->sage_location;

   

    $this->dispatchBrowserEvent('initializeAutocomplete', [
        'city' => $this->city,
        'lat' => $this->lat,
        'long' => $this->long,
        'location' => $this->location
    ]);

    $this->dispatchBrowserEvent('show-destinationEditModal');

    }




    public function update()
    {
        if ($this->destination_id) {

          
            $destination = Destination::find($this->destination_id);
            $destination->user_id = Auth::user()->id;
            $destination->country_id = $this->country_id;
            $destination->city = $this->city;
            $destination->long = $this->long;
            $destination->location = $this->location;
            $destination->lat = $this->lat;
            $destination->description = $this->description;
            $destination->status = $this->status;
            $destination->sage_location = $this->sage_location ?: null;
            $destination->update();
            $this->dispatchBrowserEvent('hide-destinationEditModal');
            $this->resetInputFields();
            $this->dispatchBrowserEvent('alert',[
                'type'=>'success',
                'message'=>"Destination Updated Successfully!!"
            ]);

       
        }
    }

    public function updatingSearch(){
        $this->resetPage();
    }

    public function render()
    {

        $sageLocations = SageProjectLocation::orderBy('name')->get();

        if (isset($this->search) && filled($this->search)) {
            return view('livewire.destinations.index',[
                'sageLocations' => $sageLocations,
                'destinations' => Destination::with('country')
                ->where('city','LIKE','%'.$this->search.'%')
                ->orWhereHas('country', function ($query) {
                    return $query->where('name','LIKE','%'.$this->search.'%');
                })
                ->orWhere('lat','like', '%'.$this->search.'%')
                ->orWhere('long','like', '%'.$this->search.'%')
                ->orWhere('description','like', '%'.$this->search.'%')
                ->orderBy('city','asc')
                ->paginate(10)
            ]);
        }else{
            return view('livewire.destinations.index',[
                'sageLocations' => $sageLocations,
                'destinations' => Destination::with('country')->orderBy('city','asc')->paginate(10)
            ]);
        }

      
      
    }
}
