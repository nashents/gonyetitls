<?php

namespace App\Http\Livewire\Destinations;

use App\Models\Country;
use Livewire\Component;
use App\Models\Destination;
use Livewire\WithPagination;
use Maatwebsite\Excel\Excel;
use App\Exports\DestinationsExport;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;

class Index extends Component
{

    use WithPagination;

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

    public $updateMode = false;
    public $deleteMode = false;

    public $destination_id;
    public $user_id;

    protected $listeners = ['setLocationData'];
 
    public function setLocationData($data)
    {
        $this->city = $data['city'];
        $this->lat = $data['lat'];
        $this->long = $data['long'];
        $this->location = $data['location'];
    }

    public function mount(){
        $this->resetPage();
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
        'country_id' => 'required',
        'city' => 'required|unique:destinations,city,NULL,id,deleted_at,NULL',
        'location' => 'required',
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

   

    $this->dispatchBrowserEvent('initializeAutocomplete', [
        'city' => $this->city,
        'lat' => $this->lat,
        'long' => $this->long,
        'location' => $this->location
    ]);

    $this->dispatchBrowserEvent('show-destinationEditModal');

    }

    private function expandUrl($url)
    {
        try {
        $response = Http::withHeaders(['User-Agent' => 'Mozilla/5.0'])
            ->get($url);

        return (string) $response->effectiveUri(); // Convert URI object to string
    } catch (\Exception $e) {
        return null;
    }
    }


    public function updatedLocation($location){
  
        
        if (!is_null($location)) {
            if ((!filled($this->lat) && !filled($this->long))) {
            
                if (strpos($location, 'maps.app.goo.gl') !== false) {
                    $fullUrl = $this->expandUrl($location);
                } else {
                    $fullUrl = $location; // Already a long URL
                }

            
                if ($fullUrl) {

               // Case 1: Extract from /search/lat,lng format
               if (preg_match('/\/search\/([-0-9.]+),\+?([-0-9.]+)/', $fullUrl, $matches)) {
               
                    $this->lat = $matches[1];
                    $this->long = $matches[2];
                }
                 // Case 2: Extract from /@lat,lng,zoom/
                elseif (preg_match('/@([-0-9.]+),([-0-9.]+),[0-9.]+z/', $fullUrl, $matches)) {
                   
                    $this->lat = $matches[1];
                    $this->long = $matches[2];
                }
                // Case 3: Extract from !3dlat!4dlong format
                elseif (preg_match('/!3d([-0-9.]+)!4d([-0-9.]+)/', $fullUrl, $matches)) {
                    $this->lat = $matches[1];
                    $this->long = $matches[2];
                }
                else {
                    $this->dispatchBrowserEvent('hide-destinationEditModal');
                    $this->dispatchBrowserEvent('hide-destinationModal');
                    $this->dispatchBrowserEvent('alert',[
                        'type'=>'error',
                        'message'=>"Could not extract coordinates. Please check the URL format."
                    ]);
                }

                } else {
                    $this->dispatchBrowserEvent('hide-destinationEditModal');
                    $this->dispatchBrowserEvent('hide-destinationModal');
                    $this->dispatchBrowserEvent('alert',[
                        'type'=>'error',
                        'message'=>"Invalid or unreachable URL."
                    ]);
                  
                }

            }
    
           
        }
        
      
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

        if (isset($this->search) && filled($this->search)) {
            return view('livewire.destinations.index',[
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
                'destinations' => Destination::with('country')->orderBy('city','asc')->paginate(10)
            ]);
        }

      
      
    }
}
